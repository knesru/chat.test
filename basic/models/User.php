<?php

namespace app\models;
use georgique\yii2\jsonrpc\JsonRpcException;

/**
 * Class User
 * @package app\models
 *
 * @property int id
 * @property string nickname
 * @property string auth_key
 * @property string access_token_hash
 * @property string password_hash
 * @property string password_reset_token
 * @property string email
 * @property string type
 * @property int group_id
 * @property string status
 * @property string status_reason
 * @property int status_user_id
 * @property int status_date
 */
class User extends ChatModel implements \yii\web\IdentityInterface
{

    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = static::findOne(['nickname' => $username]);
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key = $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $salt = substr($this->password_hash,-10);
        return sha1($password.$salt).$salt == $this->password_hash;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validateAccessToken($access_token)
    {
        $salt = substr($this->access_token_hash,-10);
        return sha1($access_token.$salt).$salt == $this->access_token_hash;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $salt = time();
        $this->password_hash = sha1($password.$salt).$salt;
    }

    public function rules()
    {
        return [
            // username and password are both required
            [['nickname', 'password'], 'required', 'on'=>['insert','update']],
            [['nickname', 'password', 'auth_key', 'access_token'], 'safe'],
            // rememberMe must be a boolean value
            //['rememberMe', 'boolean'],
            // password is validated by validatePassword()
//            ['password', 'safe'],
        ];
    }

    /**
     * @param $auth_sid
     * @param $auth_token
     * @return User|null
     * @throws JsonRpcException
     */
    public static function checkAuth($auth_sid, $auth_token)
    {
        $user = self::findOne([
            'auth_key' => $auth_sid]);
        if(is_null($user)){
            throw new JsonRpcException(null,'auth data incorrect', 1002);
        }
        if(!$user->validateAccessToken($auth_token)){
            throw new JsonRpcException(null, 'auth data incorrect or expired', 1003);
        }
        return $user;
    }
}
