<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use yii\base\Model;

/**
 * ReForm model
 * Calls for password reset and new activation link.
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class ReForm extends Model
{

    /**
     * @var string Username or email
     */
    public $username;
    
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'required'],
        ];
    }

    /**
     * Returns User.
     * @param integer $status
     * @return User
     */
    public function getUser($status = null)
    {
        if ($this->_user === false) {
            $this->_user = User::findByKeyfield($this->username, $status);
        }

        return $this->_user;
    }

    /**
     * Generates new password reset token.
     * @return boolean
     */
    public function reset()
    {
        $user = $this->getUser();
        
        if ($user) {
            $user->setScenario('token');
            $user->generatePasswordResetToken();
            return $user->save();
        }
        
        return false;
    }
    
    /**
     * Generates new activation token.
     * @return boolean
     */
    public function reactivate()
    {
        $user = $this->getUser(User::STATUS_REGISTERED);
        
        if ($user) {
            $user->setScenario('token');
            $user->generateActivationToken();
            return $user->save();
        }
        
        return false;
    }
}
