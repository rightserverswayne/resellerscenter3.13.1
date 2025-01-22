<?php
namespace MGModule\ResellersCenter\Core\Resources\Resellers;
use MGModule\ResellersCenter\Core\Resources\Promotions\Promotion;
use MGModule\ResellersCenter\repository\whmcs\Promotions as PromotionsRepo;

/**
 * Description of Promotions
 *
 * @author Paweł Złamaniec <pawel.zl@modulesgarden.com>
 */
class Promotions
{
    /**
     * @var Reseller
     */
    protected $reseller;

    /**
     * Promotions constructor.
     *
     * @param Reseller $reseller
     */
    public function __construct(Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    /**
     * Get promotion using promocode
     *
     * @param $promocode
     * @return Promotion
     */
    public function getByCode($promocode)
    {
        $promotion = new Promotion(null, $promocode, $this->reseller);
        return $promotion;
    }

    /**
     * Get promotion using promocode with reseller prefix
     *
     * @param $fullcode
     * @return Promotion
     */
    public function getByFullCode($fullcode)
    {
        //remove prefix
        $promocode = str_replace($this->getPrefix(), "", $fullcode);
        $promotion = new Promotion(null, $promocode, $this->reseller);

        return $promotion;
    }

    /**
     * Get the reseller prefix for promocodes
     *
     * @return mixed
     */
    public function getPrefix()
    {
        $result = str_replace("#", $this->reseller->id, Promotion::PREFIX);
        return $result;
    }

    /**
     * Get promotion by id
     *
     * @param $id
     * @return Promotion
     */
    public function find($id)
    {
        $promotion = new Promotion($id, null, $this->reseller);
        return $promotion;
    }

    /**
     * Get promotion data for DataTable
     *
     * @param $dtRequest
     * @param $filter
     * @return array
     */
    public function getForTable($dtRequest, $filter)
    {
        $repo = new PromotionsRepo();
        $data = $repo->getForTable($dtRequest, $this->getPrefix(), $filter);
        
        return $data;
    }
}
