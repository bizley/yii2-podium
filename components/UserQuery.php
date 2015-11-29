<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\Module as PodiumModule;
use yii\db\ActiveQuery;

/**
 * UserQuery
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class UserQuery extends ActiveQuery
{
    
    /**
     * Adds proper user ID for query.
     * @param integer $id
     */
    public function loggedUser($id)
    {
        if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
            return $this->andWhere(['inherited_id' => $id]);
        }
        else {
            return $this->andWhere(['id' => $id]);
        }
    }
}