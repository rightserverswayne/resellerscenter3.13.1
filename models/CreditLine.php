<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;
use MGModule\ResellersCenter\Core\Helpers\Reseller;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\Settings\UseCustomLimit;
use MGModule\ResellersCenter\libs\ConsolidatedInvoices\ConsolidatedInvoicesSettings\SettingsManager;
use MGModule\ResellersCenter\models\whmcs\Client;

class CreditLine extends EloquentModel
{
    protected $table = 'ResellersCenter_CreditLine';
    protected $guarded = ['id'];
    protected $fillable = ['client_id', 'reseller_id', 'limit', 'usage'];
    protected $softDelete = false;
    public $timestamps = false;

    public function __construct($clientId = null)
    {
        parent::__construct();
        $this->client_id = $clientId;
    }

    public function history()
    {
        return $this->hasMany(CreditLineHistory::class, "credit_line_id");
    }

    public function client()
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function getLimitAttribute()
    {
        $useCustomLimit = Reseller::isReseller($this->client->id) || SettingsManager::getSettingFromResellerClient($this->client->resellerClient,  UseCustomLimit::NAME);
        return $useCustomLimit ? $this->attributes['limit'] : $this->getDefaultLimit();
    }

    public function getOriginalLimitAttribute()
    {
        return $this->attributes['limit'];
    }

    protected function getDefaultLimit()
    {
        $settings = $this->client->resellerClient->reseller->settings;
        $privateSettings = $settings['private'];
        return $privateSettings['defaultCreditLineLimit'];
    }
}