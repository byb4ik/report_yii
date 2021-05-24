<?php

namespace app\models;


use DateTime;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const ROLE_ADMIN = 1;
    const ROLE_USER = 10;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'access_token', 'email'], 'required'],
            [['role'], 'integer'],
            [['username', 'password', 'email', 'password_reset_token', 'created_at', 'updated_at'], 'string', 'max' => 255],
            [['access_token', 'created_at', 'updated_at'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Пароль',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'role' => 'Role',
            'created_at' => 'Профиль создан',
            'updated_at' => 'Профиль обновлен',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    public static function roles()
    {
        return [
            self::ROLE_USER => Yii::t('app', 'User'),
            self::ROLE_ADMIN => Yii::t('app', 'Admin'),
        ];
    }

    public function getRoleName(int $id)
    {
        $list = self::roles();
        return $list[$id] ?? null;
    }

    public function isAdmin()
    {
        return ($this->role == self::ROLE_ADMIN);
    }

    public function isUser()
    {
        return ($this->role == self::ROLE_USER);
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
        $this->updated_at = date('Y-m-d H:i:m');
    }

    /**
     *
     */
    public function setCreateTime()
    {
        $this->created_at = date('Y-m-d H:i:m');
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
        $this->password_reset_token = Yii::$app->security->generatePasswordHash($key);
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function updatePassword()
    {
        $password = md5(rand(100, 999));
        $this->setPassword($password);
        $this->setUpdateTime();
        $this->validate();
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