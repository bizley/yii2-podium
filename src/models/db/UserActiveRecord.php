<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use bizley\podium\db\UserQuery;
use bizley\podium\log\Log;
use bizley\podium\models\Activity;
use bizley\podium\models\Meta;
use bizley\podium\models\Mod;
use bizley\podium\models\User;
use bizley\podium\Podium;
use Exception;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;

/**
 * User AR.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property int $id
 * @property int $inherited_id
 * @property string $username
 * @property string $slug
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $activation_token
 * @property string $email
 * @property string $new_email
 * @property string $auth_key
 * @property int $status
 * @property int $role
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Activity $activity
 * @property Meta $meta
 * @property Mod[] $mods
 * @property User[] $friends
 */
abstract class UserActiveRecord extends ActiveRecord implements IdentityInterface
{
    /**
     * Statuses.
     */
    const STATUS_REGISTERED = 1;
    const STATUS_BANNED     = 9;
    const STATUS_ACTIVE     = 10;

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
            [
                'class'     => SluggableBehavior::className(),
                'attribute' => 'username',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * Finds out if unencrypted password fulfill requirements.
     * @param string $attribute
     */
    public function passwordRequirements($attribute)
    {
        if (!preg_match('~\p{Lu}~', $this->$attribute) ||
            !preg_match('~\p{Ll}~', $this->$attribute) ||
            !preg_match('~[0-9]~', $this->$attribute) ||
            mb_strlen($this->$attribute, 'UTF-8') < 6 ||
            mb_strlen($this->$attribute, 'UTF-8') > 100) {
            $this->addError($attribute, Yii::t('podium/view', 'Password must contain uppercase and lowercase letter, digit, and be at least 6 characters long.'));
        }
    }

    /**
     * Finds registered user by activation token.
     * @param string $token activation token
     * @return static|null
     */
    public static function findByActivationToken($token)
    {
        if (!static::isActivationTokenValid($token)) {
            return null;
        }
        return static::find()->where(['activation_token' => $token, 'status' => self::STATUS_REGISTERED])->limit(1)->one();
    }

    /**
     * Finds active user by email.
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::find()->where(['email' => $email, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    /**
     * Finds active user by email token.
     * @param string $token activation token
     * @return static|null
     */
    public static function findByEmailToken($token)
    {
        if (!static::isEmailTokenValid($token)) {
            return null;
        }
        return static::find()->where(['email_token' => $token, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    /**
     * Finds user of given status by username or email.
     * @param string $keyfield value to compare
     * @param int $status
     * @return static|null
     */
    public static function findByKeyfield($keyfield, $status = self::STATUS_ACTIVE)
    {
        if ($status === null) {
            return static::find()->where(['or', ['email' => $keyfield], ['username' => $keyfield]])->limit(1)->one();
        }
        return static::find()->where(['and', ['status' => $status], ['or', ['email' => $keyfield], ['username' => $keyfield]]])->limit(1)->one();
    }

    /**
     * Finds user of given status by password reset token
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
            return static::find()->where(['password_reset_token' => $token])->limit(1)->one();
        }
        return static::find()->where(['password_reset_token' => $token, 'status' => $status])->limit(1)->one();
    }

    /**
     * Finds active user by username.
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        try {
            return static::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->limit(1)->one();
        } catch (Exception $exc) {
            Log::warning('Podium is not installed correctly!', null, __METHOD__);
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Generates new activation token.
     */
    public function generateActivationToken()
    {
        $this->activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new email token.
     */
    public function generateEmailToken()
    {
        $this->email_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
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
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Activity relation.
     * @return ActiveQuery
     */
    public function getActivity()
    {
        return $this->hasOne(Activity::className(), ['user_id' => 'id']);
    }

    /**
     * Friends relation.
     * @return ActiveQuery
     * @since 0.2
     */
    public function getFriends()
    {
        return $this->hasMany(static::className(), ['id' => 'friend_id'])->viaTable('{{%podium_user_friend}}', ['user_id' => 'id']);
    }

    /**
     * Meta relation.
     * @return ActiveQuery
     */
    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['user_id' => 'id']);
    }

    /**
     * Moderated forum relation.
     * @return ActiveQuery
     * @since 0.5
     */
    public function getMods()
    {
        return $this->hasMany(Mod::className(), ['user_id' => 'id']);
    }

    /**
     * Finds out if activation token is valid.
     * @param string $token activation token
     * @return bool
     */
    public static function isActivationTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('activation_token_expire');
        if ($expire === null) {
            $expire = 3 * 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    /**
     * Finds out if email token is valid.
     * @param string $token activation token
     * @return bool
     */
    public static function isEmailTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('email_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    /**
     * Finds out if password reset token is valid.
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        $expire = Podium::getInstance()->podiumConfig->get('password_reset_token_expire');
        if ($expire === null) {
            $expire = 24 * 60 * 60;
        }
        return static::isTokenValid($token, $expire);
    }

    /**
     * Finds out if given token type is valid.
     * @param string $token activation token
     * @param int $expire expire time
     * @return bool
     */
    public static function isTokenValid($token, $expire)
    {
        if (empty($token) || empty($expire)) {
            return false;
        }
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
        return $timestamp + (int)$expire >= time();
    }

    /**
     * Removes activation token.
     */
    public function removeActivationToken()
    {
        $this->activation_token = null;
    }

    /**
     * Removes email token.
     */
    public function removeEmailToken()
    {
        $this->email_token = null;
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Generates password hash from unencrypted password.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates current password.
     * @param string $attribute
     */
    public function validateCurrentPassword($attribute)
    {
        if (!$this->hasErrors()) {
            if (!$this->validatePassword($this->currentPassword)) {
                $this->addError($attribute, Yii::t('podium/view', 'Current password is incorrect.'));
            }
        }
    }

    /**
     * Validates password.
     * In case of inherited user component password hash is compared to
     * password hash stored in Podium::getInstance()->user->identity so this method should
     * not be used with User instance other than currently logged in one.
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $podium = Podium::getInstance();
        if ($podium->userComponent !== true) {
            $password_hash = empty($podium->userPasswordField) ? 'password_hash' : $podium->userPasswordField;
            if (!empty($podium->user->identity->$password_hash)) {
                return Yii::$app->security->validatePassword($password, $podium->user->identity->$password_hash);
            }
            return false;
        }
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Validates username.
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
}
