<?php
namespace MGModule\ResellersCenter\models\whmcs;
use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Tld
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Tld extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbltlds';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('tld');

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
    
    public function getCategories()
    {
        $repo = new \MGModule\ResellersCenter\repository\whmcs\TldCategoriesPivot();
        $pivots = $repo->getByTldId($this->id);
        $categoriesids = array();
        foreach($pivots as $pivot)
        {
            $categoriesids[] = $pivot->category_id;
        }
        
        $categorties = new \MGModule\ResellersCenter\repository\whmcs\TldCategories();
        return $categorties->getWithIds($categoriesids);
    }
}

