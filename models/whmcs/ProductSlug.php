<?php

namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

class ProductSlug extends EloquentModel
{
    protected $table = 'tblproducts_slugs';

    protected $guarded = 'id';

    protected $fillable = ['id', 'product_id', 'group_id', 'group_slug', 'slug', 'active', 'clicks', 'created_at', 'updated_at'];
}