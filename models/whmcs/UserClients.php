<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of UserClients
 * 
 * @var int id
 * @var int auth_user_id
 * @var int client_id
 * @var int invite_id
 * @var int owner
 * @var text permissions
 * @var timestamp last_login
 * @var timestamp created_at
 * @var timestamp updated_at
 *
 */
class UserClients extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblusers_clients';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('auth_user_id', 'client_id', 'invite_id', 'owner', 'permissions', 'last_login', 'created_at', 'updated_at');

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
