<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**e
 * Description of ConfigOptionLink
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class ConfigOptionLink extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblproductconfiglinks';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('gid', 'pid');

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

    /**
     * Add product id to where statement
     *
     * @param $query
     * @param $pid
     * @return mixed
     */
    public function scopeByProduct($query, $pid)
    {
        $query->where("pid", $pid);
        return $query;
    }
}
