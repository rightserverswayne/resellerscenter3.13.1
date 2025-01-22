<?php

namespace MGModule\ResellersCenter\models;

use \Illuminate\Database\Eloquent\Model as EloquentModel;

class GroupSetting extends EloquentModel
{
    protected $table = 'ResellersCenter_GroupsSettings';
    protected $guarded = ['id'];
    protected $fillable = ['group_id', 'setting', 'value'];
    protected $softDelete = false;
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(Group::class, "group_id");
    }
}