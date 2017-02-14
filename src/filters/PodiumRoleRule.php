<?php

namespace bizley\podium\filters;

use bizley\podium\models\User;
use yii\filters\AccessRule;
use yii\web\User as YiiUser;

/**
 * Podium role check access rule
 * Overrides matchRole method to ensure use of rbacComponent configuration.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class PodiumRoleRule extends AccessRule
{
    /**
     * @param YiiUser $user the user object
     * @return boolean whether the rule applies to the role
     */
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } elseif (User::can($role)) {
                return true;
            }
        }

        return false;
    }
}
