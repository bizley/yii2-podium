<?php

namespace bizley\podium\rbac;

use yii\rbac\DbManager;
use yii\rbac\Permission;
use yii\rbac\Role;

/**
 * RBAC helper
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Rbac
{
    const PERM_VIEW_THREAD     = 'viewPodiumThread';
    const PERM_VIEW_FORUM      = 'viewPodiumForum';
    const PERM_CREATE_THREAD   = 'createPodiumThread';
    const PERM_CREATE_POST     = 'createPodiumPost';
    const PERM_UPDATE_POST     = 'updatePodiumPost';
    const PERM_UPDATE_OWN_POST = 'updateOwnPodiumPost';
    const PERM_DELETE_POST     = 'deletePodiumPost';
    const PERM_DELETE_OWN_POST = 'deleteOwnPodiumPost';
    const PERM_UPDATE_THREAD   = 'updatePodiumThread';
    const PERM_DELETE_THREAD   = 'deletePodiumThread';
    const PERM_PIN_THREAD      = 'pinPodiumThread';
    const PERM_LOCK_THREAD     = 'lockPodiumThread';
    const PERM_MOVE_THREAD     = 'movePodiumThread';
    const PERM_MOVE_POST       = 'movePodiumPost';
    const PERM_BAN_USER        = 'banPodiumUser';
    const PERM_DELETE_USER     = 'deletePodiumUser';
    const PERM_PROMOTE_USER    = 'promotePodiumUser';
    const PERM_CREATE_FORUM    = 'createPodiumForum';
    const PERM_UPDATE_FORUM    = 'updatePodiumForum';
    const PERM_DELETE_FORUM    = 'deletePodiumForum';
    const PERM_CREATE_CATEGORY = 'createPodiumCategory';
    const PERM_UPDATE_CATEGORY = 'updatePodiumCategory';
    const PERM_DELETE_CATEGORY = 'deletePodiumCategory';
    const PERM_CHANGE_SETTINGS = 'changePodiumSettings';

    const ROLE_USER      = 'podiumUser';
    const ROLE_MODERATOR = 'podiumModerator';
    const ROLE_ADMIN     = 'podiumAdmin';

    /**
     * Adds RBAC rules.
     */
    public function add(DbManager $authManager)
    {
        $viewThread = $authManager->getPermission(self::PERM_VIEW_THREAD);
        if (!($viewThread instanceof Permission)) {
            $viewThread = $authManager->createPermission(self::PERM_VIEW_THREAD);
            $viewThread->description = 'View Podium thread';
            $authManager->add($viewThread);
        }

        $viewForum = $authManager->getPermission(self::PERM_VIEW_FORUM);
        if (!($viewForum instanceof Permission)) {
            $viewForum = $authManager->createPermission(self::PERM_VIEW_FORUM);
            $viewForum->description = 'View Podium forum';
            $authManager->add($viewForum);
        }

        $createThread = $authManager->getPermission(self::PERM_CREATE_THREAD);
        if (!($createThread instanceof Permission)) {
            $createThread = $authManager->createPermission(self::PERM_CREATE_THREAD);
            $createThread->description = 'Create Podium thread';
            $authManager->add($createThread);
        }

        $createPost = $authManager->getPermission(self::PERM_CREATE_POST);
        if (!($createPost instanceof Permission)) {
            $createPost = $authManager->createPermission(self::PERM_CREATE_POST);
            $createPost->description = 'Create Podium post';
            $authManager->add($createPost);
        }

        $moderatorRule = $authManager->getRule('isPodiumModerator');
        if (!($moderatorRule instanceof ModeratorRule)) {
            $moderatorRule = new ModeratorRule();
            $authManager->add($moderatorRule);
        }

        $updatePost = $authManager->getPermission(self::PERM_UPDATE_POST);
        if (!($updatePost instanceof Permission)) {
            $updatePost = $authManager->createPermission(self::PERM_UPDATE_POST);
            $updatePost->description = 'Update Podium post';
            $updatePost->ruleName    = $moderatorRule->name;
            $authManager->add($updatePost);
        }

        $authorRule = $authManager->getRule('isPodiumAuthor');
        if (!($authorRule instanceof AuthorRule)) {
            $authorRule = new AuthorRule();
            $authManager->add($authorRule);
        }

        $updateOwnPost = $authManager->getPermission(self::PERM_UPDATE_OWN_POST);
        if (!($updateOwnPost instanceof Permission)) {
            $updateOwnPost = $authManager->createPermission(self::PERM_UPDATE_OWN_POST);
            $updateOwnPost->description = 'Update own Podium post';
            $updateOwnPost->ruleName    = $authorRule->name;
            $authManager->add($updateOwnPost);
            $authManager->addChild($updateOwnPost, $updatePost);
        }

        $deletePost = $authManager->getPermission(self::PERM_DELETE_POST);
        if (!($deletePost instanceof Permission)) {
            $deletePost = $authManager->createPermission(self::PERM_DELETE_POST);
            $deletePost->description = 'Delete Podium post';
            $deletePost->ruleName    = $moderatorRule->name;
            $authManager->add($deletePost);
        }

        $deleteOwnPost = $authManager->getPermission(self::PERM_DELETE_OWN_POST);
        if (!($deleteOwnPost instanceof Permission)) {
            $deleteOwnPost = $authManager->createPermission(self::PERM_DELETE_OWN_POST);
            $deleteOwnPost->description = 'Delete own Podium post';
            $deleteOwnPost->ruleName    = $authorRule->name;
            $authManager->add($deleteOwnPost);
            $authManager->addChild($deleteOwnPost, $deletePost);
        }

        $user = $authManager->getRole(self::ROLE_USER);
        if (!($user instanceof Role)) {
            $user = $authManager->createRole(self::ROLE_USER);
            $authManager->add($user);
            $authManager->addChild($user, $viewThread);
            $authManager->addChild($user, $viewForum);
            $authManager->addChild($user, $createThread);
            $authManager->addChild($user, $createPost);
            $authManager->addChild($user, $updateOwnPost);
            $authManager->addChild($user, $deleteOwnPost);
        }

        $updateThread = $authManager->getPermission(self::PERM_UPDATE_THREAD);
        if (!($updateThread instanceof Permission)) {
            $updateThread = $authManager->createPermission(self::PERM_UPDATE_THREAD);
            $updateThread->description = 'Update Podium thread';
            $updateThread->ruleName    = $moderatorRule->name;
            $authManager->add($updateThread);
        }

        $deleteThread = $authManager->getPermission(self::PERM_DELETE_THREAD);
        if (!($deleteThread instanceof Permission)) {
            $deleteThread = $authManager->createPermission(self::PERM_DELETE_THREAD);
            $deleteThread->description = 'Delete Podium thread';
            $deleteThread->ruleName    = $moderatorRule->name;
            $authManager->add($deleteThread);
        }

        $pinThread = $authManager->getPermission(self::PERM_PIN_THREAD);
        if (!($pinThread instanceof Permission)) {
            $pinThread = $authManager->createPermission(self::PERM_PIN_THREAD);
            $pinThread->description = 'Pin Podium thread';
            $pinThread->ruleName    = $moderatorRule->name;
            $authManager->add($pinThread);
        }

        $lockThread = $authManager->getPermission(self::PERM_LOCK_THREAD);
        if (!($lockThread instanceof Permission)) {
            $lockThread = $authManager->createPermission(self::PERM_LOCK_THREAD);
            $lockThread->description = 'Lock Podium thread';
            $lockThread->ruleName    = $moderatorRule->name;
            $authManager->add($lockThread);
        }

        $moveThread = $authManager->getPermission(self::PERM_MOVE_THREAD);
        if (!($moveThread instanceof Permission)) {
            $moveThread = $authManager->createPermission(self::PERM_MOVE_THREAD);
            $moveThread->description = 'Move Podium thread';
            $moveThread->ruleName    = $moderatorRule->name;
            $authManager->add($moveThread);
        }

        $movePost = $authManager->getPermission(self::PERM_MOVE_POST);
        if (!($movePost instanceof Permission)) {
            $movePost = $authManager->createPermission(self::PERM_MOVE_POST);
            $movePost->description = 'Move Podium post';
            $movePost->ruleName    = $moderatorRule->name;
            $authManager->add($movePost);
        }

        $banUser = $authManager->getPermission(self::PERM_BAN_USER);
        if (!($banUser instanceof Permission)) {
            $banUser = $authManager->createPermission(self::PERM_BAN_USER);
            $banUser->description = 'Ban Podium user';
            $authManager->add($banUser);
        }

        $moderator = $authManager->getRole(self::ROLE_MODERATOR);
        if (!($moderator instanceof Role)) {
            $moderator = $authManager->createRole(self::ROLE_MODERATOR);
            $authManager->add($moderator);
            $authManager->addChild($moderator, $updatePost);
            $authManager->addChild($moderator, $updateThread);
            $authManager->addChild($moderator, $deletePost);
            $authManager->addChild($moderator, $deleteThread);
            $authManager->addChild($moderator, $pinThread);
            $authManager->addChild($moderator, $lockThread);
            $authManager->addChild($moderator, $moveThread);
            $authManager->addChild($moderator, $movePost);
            $authManager->addChild($moderator, $banUser);
            $authManager->addChild($moderator, $user);
        }

        $deleteUser = $authManager->getPermission(self::PERM_DELETE_USER);
        if (!($deleteUser instanceof Permission)) {
            $deleteUser = $authManager->createPermission(self::PERM_DELETE_USER);
            $deleteUser->description = 'Delete Podium user';
            $authManager->add($deleteUser);
        }

        $promoteUser = $authManager->getPermission(self::PERM_PROMOTE_USER);
        if (!($promoteUser instanceof Permission)) {
            $promoteUser = $authManager->createPermission(self::PERM_PROMOTE_USER);
            $promoteUser->description = 'Promote Podium user';
            $authManager->add($promoteUser);
        }

        $createForum = $authManager->getPermission(self::PERM_CREATE_FORUM);
        if (!($createForum instanceof Permission)) {
            $createForum = $authManager->createPermission(self::PERM_CREATE_FORUM);
            $createForum->description = 'Create Podium forum';
            $authManager->add($createForum);
        }

        $updateForum = $authManager->getPermission(self::PERM_UPDATE_FORUM);
        if (!($updateForum instanceof Permission)) {
            $updateForum = $authManager->createPermission(self::PERM_UPDATE_FORUM);
            $updateForum->description = 'Update Podium forum';
            $authManager->add($updateForum);
        }

        $deleteForum = $authManager->getPermission(self::PERM_DELETE_FORUM);
        if (!($deleteForum instanceof Permission)) {
            $deleteForum = $authManager->createPermission(self::PERM_DELETE_FORUM);
            $deleteForum->description = 'Delete Podium forum';
            $authManager->add($deleteForum);
        }

        $createCategory = $authManager->getPermission(self::PERM_CREATE_CATEGORY);
        if (!($createCategory instanceof Permission)) {
            $createCategory = $authManager->createPermission(self::PERM_CREATE_CATEGORY);
            $createCategory->description = 'Create Podium category';
            $authManager->add($createCategory);
        }

        $updateCategory = $authManager->getPermission(self::PERM_UPDATE_CATEGORY);
        if (!($updateCategory instanceof Permission)) {
            $updateCategory = $authManager->createPermission(self::PERM_UPDATE_CATEGORY);
            $updateCategory->description = 'Update Podium category';
            $authManager->add($updateCategory);
        }

        $deleteCategory = $authManager->getPermission(self::PERM_DELETE_CATEGORY);
        if (!($deleteCategory instanceof Permission)) {
            $deleteCategory = $authManager->createPermission(self::PERM_DELETE_CATEGORY);
            $deleteCategory->description = 'Delete Podium category';
            $authManager->add($deleteCategory);
        }

        $settings = $authManager->getPermission(self::PERM_CHANGE_SETTINGS);
        if (!($settings instanceof Permission)) {
            $settings = $authManager->createPermission(self::PERM_CHANGE_SETTINGS);
            $settings->description = 'Change Podium settings';
            $authManager->add($settings);
        }

        $admin = $authManager->getRole(self::ROLE_ADMIN);
        if (!($admin instanceof Role)) {
            $admin = $authManager->createRole(self::ROLE_ADMIN);
            $authManager->add($admin);
            $authManager->addChild($admin, $deleteUser);
            $authManager->addChild($admin, $promoteUser);
            $authManager->addChild($admin, $createForum);
            $authManager->addChild($admin, $updateForum);
            $authManager->addChild($admin, $deleteForum);
            $authManager->addChild($admin, $createCategory);
            $authManager->addChild($admin, $updateCategory);
            $authManager->addChild($admin, $deleteCategory);
            $authManager->addChild($admin, $settings);
            $authManager->addChild($admin, $moderator);
        }
    }
}
