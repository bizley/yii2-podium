<?php

namespace bizley\podium\models\forms;

use bizley\podium\models\User;
use bizley\podium\Podium;
use yii\base\Model;

/**
 * LoginForm model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property User $user
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
     * @var bool Remember me flag
     */
    public $rememberMe = false;

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
            $user = $this->user;
            if (empty($user) || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs user in.
     * @return bool
     */
    public function login()
    {
        if ($this->validate()) {
            return Podium::getInstance()->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    private $_user = false;

    /**
     * Returns user.
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
