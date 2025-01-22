<?php
namespace MGModule\ResellersCenter\models;
use \Illuminate\Database\Eloquent\Model as EloquentModel;
use MGModule\ResellersCenter\models\source\ModelException;

/**
 * Description of Settings
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ResellerSetting extends EloquentModel
{
     /**
     * Table name
     *
     * @var string
     */
    protected $table = 'ResellersCenter_ResellersSettings';

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('reseller_id', 'setting', 'value');

    /**
     * Primary keys
     * @var array
     */
//    protected $primaryKey = array('reseller_id', 'setting');
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array();
    
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
    public $timestamps = false;
    
    public function fillData($rid, $setting, $value, $private)
    {
        if(empty($rid) && $rid != 0){
            throw new ModelException("invalid_resellerid");
        }
        
        if(empty($setting)){
            throw new ModelException("invalid_setting");
        }

        $this->reseller_id = $rid;
        $this->private  = $private;
        $this->setting  = $setting;
        $this->value    = $value;
    }
}
