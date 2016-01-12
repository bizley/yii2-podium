<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class LoginForm extends Model
{

    /**
     * @var string Username or email
     */
    public $username;
    
    /**
     * @var string Password
     */
    public $password;
    
    /**
     * @var boolean Remember me flag
     */
    public $rememberMe = false;
    
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'string', 'min' => '3'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates password.
     * @param string $attribute
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs user in.
     * @return boolean
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Return User.
     * @return User
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByKeyfield($this->username);
        }
        return $this->_user;
    }
}
