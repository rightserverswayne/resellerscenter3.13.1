<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

use MGModule\ResellersCenter\repository\ResellersSettings;
use MGModule\ResellersCenter\models\source\ModelException;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of Reseller
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Reseller extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_Resellers';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = ['client_id', 'group_id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
    
    /**
     * Indicates if the model should soft delete.
     *
     * @var bool
     */
    protected $softDelete = false;
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    
    
    public function fillData($clientid, $groupid)
    {
        $this->client_id = $clientid;
        $this->group_id = $groupid;
    }
    
    public function findByClientId($clientid)
    {
        return $this->where("client_id", $clientid)->first();
    }
    
    public function client()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Client", "id", "client_id");
    }
    
    public function group()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\Group");
    }
    
    public function assignedClients()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\ResellerClient");
    }
    
    public function services()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\ResellerService");
    }
    
    public function profits()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\ResellerProfit");
    }
    
    public function RCInvoices()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\Invoice");
    }
    
    public function getSettingsAttribute()
    {
        $result = [];
        
        $repo = new ResellersSettings();
        $private = $repo->getSettings($this->attributes["id"], true);
        foreach ($private as $key => $setting) {
            $result["private"][$key] = $setting;
        }
        
        $admin = $repo->getSettings($this->attributes["id"]);
        foreach ($admin as $key => $setting) {
            $result["admin"][$key] = $setting;
        }
        
        return $result;
    }
    
    public function scopeWithFilter($query, $filter)
    {
        $query->leftjoin('tblclients', function($join) {
            $join->on("tblclients.id", "=", DB::raw("ResellersCenter_Resellers.client_id"));
        });
        
        if (!empty($filter)) {
            $query->where(function($query) use($filter)
            {
                $query->orWhere("tblclients.firstname", "LIKE", "%$filter%")
                      ->orWhere("tblclients.lastname", "LIKE", "%$filter%")
                      ->orWhere("ResellersCenter_Resellers.id", "LIKE", "%$filter%");
            });
        }
        
        $query->select("ResellersCenter_Resellers.*");
        return $query;
    }
}
