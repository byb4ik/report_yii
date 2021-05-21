<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    /**
     * User model
     *
     * @property integer $id
     * @property string $username
     * @property string $password write-only password
     * @property string $access_token
     * @property string $auth_key
     * @property string $password_reset_token
     * @property string $email
     * @property string $role
     * @property integer $created_at
     * @property integer $updated_at
     */

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'access_token', 'email', 'created_at', 'updated_at'], 'required'],
            [['role'], 'integer'],
            [['username', 'password', 'email', 'created_at', 'updated_at'], 'string', 'max' => 255],
            [['access_token', 'password_reset_token'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validateToken($token)
    {
        $tokenTrue = explode('!-!', $this->password_reset_token)['0'];
        return Yii::$app->security->validatePassword($token, $tokenTrue);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     *
     */
    public function setUpdateTime()
    {
        $this->updated_at = date('Y-m-d H:m:i');
    }

    /**
     *
     */
    public function setCreateTime()
    {
        $this->created_at = date('Y-m-d H:m:i');
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function generatePasswordResetToken($key)
    {
        $this->password_reset_token = Yii::$app->security->generatePasswordHash($key) . '!-!' . date('Y-m-d H:m:i');
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function updatePassword(){
        $password = md5(rand(100, 999));
        $this->setPassword($password);
        $this->setUpdateTime();
        $this->save();
        $emailData = [
            'email' => $this->email,
            'title' => 'Новый пароль',
            'data' => $password,
            'name' => $this->username,
        ];
        SendMail::sendEmail($emailData);

        return true;
    }
}