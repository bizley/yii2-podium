<?php

namespace bizley\podium\models;

use bizley\podium\Podium;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ReForm model
 * Calls for password reset and new activation link.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class ReForm extends Model
{
    /**
     * Responses.
     */
    const RESP_ERR = 0;
    const RESP_OK = 1;
    const RESP_EMAIL_SEND_ERR = 2;
    const RESP_NO_EMAIL_ERR = 3;
    const RESP_NO_USER_ERR = 4;
    
    /**
     * @var string Username or email
     */
    public $username;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'required'],
        ];
    }

    private $_user = false;
    
    /**
     * Returns User.
     * @param int $status
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
     * @return int
     */
    public function reset()
    {
        $user = $this->getUser();
        
        if (empty($user)) {
            return self::RESP_NO_USER_ERR;
        }

        $user->scenario = 'token';
        $user->generatePasswordResetToken();
        if ($user->save()) {
            if ($user->email) {
                if ($this->sendResetEmail($user)) {
                    return self::RESP_OK;
                }
                return self::RESP_EMAIL_SEND_ERR;
            }
            return self::RESP_NO_EMAIL_ERR;
        }        
        return self::RESP_ERR;
    }
    
    /**
     * Generates new activation token.
     * @return int
     */
    public function reactivate()
    {
        $user = $this->getUser(User::STATUS_REGISTERED);
        
        if (empty($user)) {
            return self::RESP_NO_USER_ERR;
        }
        
        $user->scenario = 'token';
        $user->generateActivationToken();
        if ($user->save()) {
            if ($user->email) {
                if ($this->sendReactivationEmail($user)) {
                    return self::RESP_OK;
                }
                return self::RESP_EMAIL_SEND_ERR;
            }
            return self::RESP_NO_EMAIL_ERR;
        }        
        return self::RESP_ERR;
    }
    
    /**
     * Sends reactivation email.
     * @param User $user
     * @return bool
     * @since 0.2
     */
    protected function sendReactivationEmail(User $user)
    {
        $forum = Podium::getInstance()->podiumConfig->get('name');
        $email = Content::fill(Content::EMAIL_REACTIVATION);
        if ($email !== false) {
            $link = Url::to(['account/activate', 'token' => $user->activation_token], true);        
            return Email::queue(
                $user->email, 
                str_replace('{forum}', $forum, $email->topic),
                str_replace('{forum}', $forum, str_replace('{link}', 
                    Html::a($link, $link), $email->content)), 
                !empty($user->id) ? $user->id : null
            );
        }
        return false;
    }
    
    /**
     * Sends reset email.
     * @param User $user
     * @return bool
     * @since 0.2
     */
    protected function sendResetEmail(User $user)
    {
        $forum = Podium::getInstance()->podiumConfig->get('name');
        $email = Content::fill(Content::EMAIL_PASSWORD);
        if ($email !== false) {
            $link = Url::to(['account/password', 'token' => $user->password_reset_token], true);        
            return Email::queue(
                $user->email, 
                str_replace('{forum}', $forum, $email->topic),
                str_replace('{forum}', $forum, str_replace('{link}', 
                    Html::a($link, $link), $email->content)), 
                !empty($user->id) ? $user->id : null
            );
        }
        return false;
    }
}
