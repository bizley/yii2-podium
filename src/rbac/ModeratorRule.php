<?php

namespace bizley\podium\rbac;

use yii\rbac\Rule;

/**
 * Checks if authorID matches user passed via params
 */
class ModeratorRule extends Rule
{
    public $name = 'isModerator';

    /**
     * @param string|integer $user the user ID.
     * @param \yii\rbac\Item $item the role or permission that this rule is associated with
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['item']) ? $params['item']->isMod() : false;
    }
}