<?php

namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class Quote extends EloquentModel
{
    protected $table = 'tblquotes';
    protected $guarded = ['id'];
    protected $fillable = ['userid', 'subject', 'stage', 'validuntil', 'firstname', 'lastname', 'companyname', 'email', 'address1', 'address2', 'city', 'state', 'postcode', 'country', 'phonenumber', 'tax_id', 'currency', 'subtotal', 'tax1', 'tax2', 'total', 'proposal', 'customernotes', 'adminnotes', 'datecreated', 'lastmodified', 'datesent', 'dateaccepted'];
    protected $softDelete = false;
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany("MGModule\ResellersCenter\models\whmcs\QuoteItem", "quoteid");
    }

    public function client()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Client", "userid");
    }

    public function currency()
    {
        return $this->hasOne("MGModule\ResellersCenter\models\whmcs\Currency", "currency");
    }

}