<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class CreditLineHistory extends EloquentModel
{
    protected $table = 'ResellersCenter_CreditLineHistory';
    protected $guarded = ['id'];
    protected $fillable = ['credit_line_id', 'balance', 'amount', 'invoice_item_id', 'invoice_type', 'date'];
    protected $softDelete = false;
    public $timestamps = false;

    public function creditLine()
    {
        return $this->belongsTo(CreditLine::class, 'credit_line_id');
    }

}