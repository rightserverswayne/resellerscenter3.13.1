<?php
namespace MGModule\ResellersCenter\models\whmcs;

use \Illuminate\Database\Eloquent\model as EloquentModel;

/**
 * Description of Invite
 * 
 * @var int id
 * @var string first_name
 * @var string last_name
 * @var string email
 * @var string password
 * @var string language
 * @var string second_factor
 * @var text second_factor_config
 * @var string remember_token
 * @var string reset_token
 * @var int security_question_id
 * @var string security_question_answer
 * @var string last_ip
 * @var string last_hostname
 * @var timestamp last_login
 * @var string email_verification_token
 * @var timestamp email_verification_token_expiry
 * @var timestamp email_verified_at
 * @var timestamp reset_token_expiry
 * @var timestamp created_at
 * @var timestamp updated_at
 *
 */
class User extends EloquentModel
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'tblusers';

    /**
     * Eloquent guarded parameters
     * @var array
     */
    protected $guarded = array('id');

    /**
     * Eloquent fillable parameters
     * @var array
     */
    protected $fillable = array('first_name', 'last_name', 'email', 'password', 'language', 'second_factor', 'second_factor_config', 'remember_token', 'reset_token', 'security_question_id', 'security_question_answer', 'last_ip', 'last_hostname', 'last_login', 'email_verification_token', 'email_verification_token_expiry', 'email_verified_at', 'reset_token_expiry', 'created_at', 'updated_at');

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
