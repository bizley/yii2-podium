<?php

namespace bizley\podium\web;

use bizley\podium\Podium;
use yii\rbac\CheckAccessInterface;
use yii\web\User as YiiUser;

/**
 * Podium User component.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */
class User extends YiiUser
{
    /**
     * Returns the access checker used for checking access.
     * @return CheckAccessInterface
     */
    protected function getAccessChecker()
    {
        return Podium::getInstance()->rbac;
    }
}