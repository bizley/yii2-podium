<?php

namespace bizley\podium\filters;

use bizley\podium\Podium;
use Yii;

/**
 * Permission denied access rule
 * Redirects user with error message in case of no permission granted.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class LoginRequiredRule extends PodiumRoleRule
{
    /**
     * @var boolean whether this is an 'allow' rule or 'deny' rule.
     */
    public $allow = false;

    /**
     * @var array list of roles that this rule applies to.
     */
    public $roles = ['?'];

    /**
     * @var string message.
     */
    public $message;

    /**
     * @var string type of message.
     */
    public $type = 'warning';

    /**
     * Sets deny callback.
     */
    public function init()
    {
        parent::init();
        $this->denyCallback = function () {
            Yii::$app->session->addFlash($this->type, $this->message, true);
            return Yii::$app->response->redirect([Podium::getInstance()->prepareRoute('account/login')]);
        };
    }
}
