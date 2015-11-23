<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use yii\base\Model;

class ReForm extends Model
{

    public $username;
    private $_user = false;

    public function rules()
    {
        return [
            ['username', 'required'],
        ];
    }

    public function getUser($status = null)
    {
        if ($this->_user === false) {
            $this->_user = User::findByKeyfield($this->username, $status);
        }

        return $this->_user;
    }

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
