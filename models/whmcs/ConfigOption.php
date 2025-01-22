<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of ConfigOptionGroup
 * 
 * @var id
 * @var gid
 * @var optionname
 * @var optiontype
 * @var qtyminimum
 * @var qtymaximium
 * @var order
 * @var hidden
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOption extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproductconfigoptions';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('gid', 'optionname', 'optiontype', 'qtyminimum', 'qtymaximium', 'order', 'hidden');

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

    /**
     * Suboptions relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suboptions()
    {
        return $this->hasMany(ConfigOptionSub::class, "configid");
    }

    /**
     * Get config options by group
     *
     * @param $query
     * @param $gid
     * @return mixed
     */
    public function scopeByGroup($query, $gid)
    {
        $query->where("gid", $gid);
        return $query;
    }

    /**
     * Get config options that belongs to groups
     *
     * @param $query
     * @param array $gids
     * @return mixed
     */
    public function scopeByInGroup($query, array $gids)
    {
        $query->whereIn("gid", $gids);
        return $query;
    }
}
