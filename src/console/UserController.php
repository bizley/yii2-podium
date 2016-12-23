<?php

namespace bizley\podium\console;

use yii\console\Controller;
use bizley\podium\Podium;
use bizley\podium\models\User;

/**
 * Podium command line interface to RBAC managment
 * 
 * @author pavlm
 */
class UserController extends Controller
{

    /**
     * Changes forum user role.
     * @param int|string $idOrEmail internal podium user id or email
     * @param string $role one of 'podiumUser', 'podiumModerator', 'podiumAdmin' or other application defined role 
     */
    public function actionAssignRole($idOrEmail, $role)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return self::EXIT_CODE_ERROR;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('No such role.' . PHP_EOL);
            return self::EXIT_CODE_ERROR;
        }
        if (strpos($role->name, 'podium') === 0) {
            // remove another podium role
            $userRoles = $rbac->getRolesByUser($user->id);
            $podiumRoles = array_filter($userRoles, function ($role) {
                return strpos($role->name, 'podium') === 0;
            });
            foreach ($podiumRoles as $podiumRole) {
                $rbac->revoke($podiumRole, $user->id);
            }
        }
        $rbac->assign($role, $user->id);
    }

    /**
     * Revokes forum user role.
     * @param int|string $idOrEmail internal podium user id or email
     * @param string $role
     */
    public function actionRevokeRole($idOrEmail, $role)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return self::EXIT_CODE_ERROR;
        }
        $rbac = Podium::getInstance()->getRbac();
        if (!$role = $rbac->getRole($role)) {
            $this->stderr('No such role.' . PHP_EOL);
            return self::EXIT_CODE_ERROR;
        }
        $rbac->revoke($role, $user->id);
    }
    
    /**
     * Shows user roles
     * @param int|string $idOrEmail internal podium user id or email
     */
    public function actionShowRoles($idOrEmail)
    {
        if (!$user = $this->findUser($idOrEmail)) {
            return self::EXIT_CODE_ERROR;
        }
        $roles = Podium::getInstance()->getRbac()->getRolesByUser($user->id);
        print_r($roles);
    }
    
    /**
     * Finds user by id or email
     * @param int|string $idOrEmail internal podium user id or email
     */
    protected function findUser($idOrEmail)
    {
        if (!$user = User::find()->andWhere(is_numeric($idOrEmail) ? ['id' => $idOrEmail] : ['email' => $idOrEmail])->limit(1)->one()) {
            $this->stderr('User not found.' . PHP_EOL);
        }
        return $user;
    }
}