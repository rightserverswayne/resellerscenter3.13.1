<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class ResellersClientsSetting extends EloquentModel
{
    protected $table = 'ResellersCenter_ResellersClientsSettings';
    protected $guarded = ['id'];
    protected $fillable = ['reseller_client_id', 'setting', 'value'];
    protected $softDelete = false;
    public $timestamps = false;

    public function resellerClient()
    {
        return $this->belongsTo(ResellerClient::class, "reseller_client_id");
    }
}