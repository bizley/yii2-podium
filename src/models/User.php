<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\Podium;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $slug
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $activation_token
 * @property string $email
 * @property string $new_email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $current_password write-only password
 * @property string $password write-only password
 * @property string $password_repeat write-only password repeated
 * @property integer $tos write-only terms of service agreement
 */
class User extends ActiveRecord implements IdentityInterface
{

    const STATUS_REGISTERED = 1;
    const STATUS_BANNED     = 9;
    const STATUS_ACTIVE     = 10;
    const ROLE_MEMBER       = 1;
    const ROLE_MODERATOR    = 9;
    const ROLE_ADMIN        = 10;

    public $password;
    public $password_repeat;
    public $current_password;
    public $tos;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            SluggableBehavior::className(),
        ];
    }

    public function scenarios()
    {
        return [
            'installation'   => [],
            'token'          => [],
            'passwordChange' => ['password', 'password_repeat'],
            'account'        => ['username', 'new_email', 'password', 'password_repeat', 'current_password'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password', 'password_repeat', 'tos'], 'required', 'except' => ['account']],
            ['current_password', 'required'],
            ['current_password', 'validateCurrentPassword'],
            [['email', 'new_email'], 'email', 'message' => Yii::t('podium/view', 'This is not a valid e-mail address.')],
            [['email', 'new_email'], 'string', 'max' => 255, 'message' => Yii::t('podium/view', 'Provided e-mail address is too long.')],
            ['email', 'unique'],
            ['new_email', 'unique', 'targetAttribute' => 'email'],
            ['password', 'passwordRequirements'],
            ['password', 'compare'],
            ['username', 'unique'],
            ['username', 'validateUsername'],
            ['status', 'default', 'value' => self::STATUS_REGISTERED],
            ['role', 'default', 'value' => self::ROLE_MEMBER],
            ['tos', 'in', 'range' => [1], 'message' => Yii::t('podium/view', 'You have to read and agree on ToS.')]
        ];
    }
    
    /**
     * Validates username
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateUsername($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\p{L}][\w\p{L}]{2,254}$/u', $this->username)) {
                $this->addError($attribute, Yii::t('podium/view', 'Username must start with a letter, contain only letters, digits and underscores, and be at least 3 characters long.'));
            }
        }
    }
    
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['user_id' => 'id']);
    }

    public function passwordRequirements()
    {
        if (!preg_match('~\p{Lu}~', $this->password) ||
                !preg_match('~\p{Ll}~', $this->password) ||
                !preg_match('~[0-9]~', $this->password) ||
                mb_strlen($this->password, 'UTF-8') < 6 ||
                mb_strlen($this->password, 'UTF-8') > 100) {
            $this->addError('password', Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'));
        }
    }
    
    public function validateCurrentPassword($attribute)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->current_password)) {
                $this->addError($attribute, Yii::t('podium/view', 'Current password is incorrect.'));
            }
        }
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE     => Yii::t('podium/view', 'Active'),
            self::STATUS_BANNED     => Yii::t('podium/view', 'Banned'),
            self::STATUS_REGISTERED => Yii::t('podium/view', 'Registered'),
        ];
    }

    public static function getRoles()
    {
        return [
            self::ROLE_MEMBER    => Yii::t('podium/view', 'Member'),
            self::ROLE_MODERATOR => Yii::t('podium/view', 'Moderator'),
            self::ROLE_ADMIN     => Yii::t('podium/view', 'Admin'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username or email
     *
     * @param string $keyfield
     * @return static|null
     */
    public static function findByKeyfield($keyfield, $status = self::STATUS_ACTIVE)
    {
        if ($status === null) {
            return static::find()->where(['or', 'email=:key', 'username=:key'])->params([':key' => $keyfield])->one();
        }
        return static::find()->where(['and', 'status=:status', ['or', 'email=:key',
                        'username=:key']])->params([':status' => $status, ':key' => $keyfield])->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @param string|null $status user status or null
     * @return static|null
     */
    public static function findByPasswordResetToken($token, $status = self::STATUS_ACTIVE)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        if ($status == null) {
            return static::findOne(['password_reset_token' => $token]);
        }
        return static::findOne(['password_reset_token' => $token, 'status' => $status]);
    }

    /**
     * Finds user by activation token
     *
     * @param string $token activation token
     * @return static|null
     */
    public static function findByActivationToken($token)
    {
        if (!static::isActivationTokenValid($token)) {
            return null;
        }

        return static::findOne(['activation_token' => $token, 'status' => self::STATUS_REGISTERED]);
    }

    public static function findByEmailToken($token)
    {
        if (!static::isEmailTokenValid($token)) {
            return null;
        }

        return static::findOne(['email_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        return self::isTokenValid($token, Podium::getInstance()->getParam('passwordResetTokenExpire', 24 * 60 * 60));
    }
    
    public static function isEmailTokenValid($token)
    {
        return self::isTokenValid($token, Podium::getInstance()->getParam('emailTokenExpire', 24 * 60 * 60));
    }

    /**
     * Finds out if activation token is valid
     *
     * @param string $token activation token
     * @return boolean
     */
    public static function isActivationTokenValid($token)
    {
        return self::isTokenValid($token, Podium::getInstance()->getParam('activationTokenExpire', 3 * 24 * 60 * 60));
    }
    
    public static function isTokenValid($token, $expire)
    {
        if (empty($token)) {
            return false;
        }
        $parts     = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
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
    public function getAuthKey()
    {
        return $this->auth_key;
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
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    /**
     * Generates new email token
     */
    public function generateEmailToken()
    {
        $this->email_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    /**
     * Removes email token
     */
    public function removeEmailToken()
    {
        $this->email_token = null;
    }

    /**
     * Generates new activation token
     */
    public function generateActivationToken()
    {
        $this->activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes activation token
     */
    public function removeActivationToken()
    {
        $this->activation_token = null;
    }

    public function register()
    {
        $this->setPassword($this->password);
        $this->generateActivationToken();
        $this->generateAuthKey();
        $this->status = self::STATUS_REGISTERED;

        return $this->save();
    }

    public function activate()
    {
        if ($this->status == self::STATUS_REGISTERED) {
            $this->removeActivationToken();
            $this->status = self::STATUS_ACTIVE;

            return $this->save();
        }

        return false;
    }

    public function changePassword()
    {
        $this->setPassword($this->password);
        $this->generateAuthKey();
        $this->removePasswordResetToken();

        return $this->save();
    }
    
    public function changeEmail()
    {
        $this->email = $this->new_email;
        $this->new_email = null;
        $this->removeEmailToken();

        return $this->save();
    }

    public function saveChanges()
    {
        if ($this->password) {
            $this->setPassword($this->password);
        }
        if ($this->new_email) {
            $this->generateEmailToken();
        }
        
        return $this->save();
    }
    
    public function getPodiumName()
    {
        return $this->username ? $this->username : Yii::t('podium/view', 'Member#{ID}', ['ID' => $this->id]);
    }
}