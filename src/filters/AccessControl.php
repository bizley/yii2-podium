<?php

namespace bizley\podium\filters;

use bizley\podium\Podium;
use yii\filters\AccessControl as YiiAccessControl;

/**
 * Podium access control filter
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class AccessControl extends YiiAccessControl
{
    /**
     * @var array the default configuration of access rules. Individual rule
     * configurations specified via rules will take precedence when the same
     * property of the rule is configured.
     */
    public $ruleConfig = ['class' => 'bizley\podium\filters\PodiumRoleRule'];

    /**
     * Sets Podium user component.
     */
    public function init()
    {
        $this->user = Podium::getInstance()->user;
        parent::init();
    }
}
