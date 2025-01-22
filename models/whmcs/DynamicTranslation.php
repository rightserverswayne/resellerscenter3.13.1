<?php

namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class DynamicTranslation extends EloquentModel
{
    protected $table = 'tbldynamic_translations';
    protected $guarded = ['id'];
    protected $fillable = ['related_type', 'related_id', 'language', 'translation', 'input_type'];
    protected $softDelete = false;
    public $timestamps = false;

}