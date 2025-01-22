<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Invite
 * 
 * @var int id
 * @var string token
 * @var string email
 * @var int client_id
 * @var int invited_by
 * @var int invited_by_admin
 * @var text permissions
 * @var timestamp accepted_at
 * @var timestamp created_at
 * @var timestamp updated_at
 * @var timestamp deleted_at
 *
 */
class Invite extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tbluser_invites';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('token', 'email', 'client_id', 'invited_by', 'invited_by_admin', 'permissions', 'accepted_at', 'created_at', 'updated_at', 'deleted_at');

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
    public $timestamps = false;
}
