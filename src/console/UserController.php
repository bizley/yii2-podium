<?php

namespace bizley\podium\console;

use yii\console\Controller;
use bizley\podium\Podium;
use bizley\podium\models\User;

/**
 * RBAC cli
 * @author pavlm
 */
class UserController extends Controller
{

    /**
     * @param integer|string $idOrEmail
     * @param string $role
     */
    public function actionAssignRole($idOrEmail, $role)
    {
        if (!$user = is_numeric($idOrEmail) ? User::findOne($idOrEmail) : User::findOne(['email' => $idOrEmail])) {
            $this->stderr('no user found' . PHP_EOL);
            return 1;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('no such role' . PHP_EOL);
            return 1;
        }
        $rbac->assign($role, $user->id);
    }

    /**
     * @param integer|string $idOrEmail
     * @param string $role
     */
    public function actionRevokeRole($idOrEmail, $role)
    {
        if (!$user = is_numeric($idOrEmail) ? User::findOne($idOrEmail) : User::findOne(['email' => $idOrEmail])) {
            $this->stderr('no user found' . PHP_EOL);
            return 1;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('no such role' . PHP_EOL);
            return 1;
        }
        $rbac->revoke($role, $user->id);
    }
    
    /**
     * @param integer|string $idOrEmail
     */
    public function actionShowRoles($idOrEmail)
    {
        if (!$user = is_numeric($idOrEmail) ? User::findOne($idOrEmail) : User::findOne(['email' => $idOrEmail])) {
            $this->stderr('no user found' . PHP_EOL);
            return 1;
        }
        $roles = Podium::getInstance()->getRbac()->getRolesByUser($user->id);
        print_r($roles);
    }
    
}