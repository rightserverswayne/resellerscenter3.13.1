<?php

namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class QuoteItem extends EloquentModel
{
    protected $table = 'tblquoteitems';
    protected $guarded = ['id'];
    protected $fillable = ['quoteid', 'description', 'quantity', 'unitprice', 'discount', 'taxable', 'created_at', 'updated_at' ];
    protected $softDelete = false;
    public $timestamps = false;

    public function quote()
    {
        return $this->belongsTo("MGModule\ResellersCenter\models\whmcs\Quote", "quoteid");
    }

}