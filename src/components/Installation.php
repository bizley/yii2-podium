<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\models\User;
use bizley\podium\rbac\AuthorRule;
use bizley\podium\rbac\ModeratorRule;
use Exception;
use Yii;
use yii\db\Schema;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * Podium Installation
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\DbManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Installation extends Maintenance
{

    /**
     * Adds Administrator account.
     * @return string result message.
     */
    protected function _addAdmin()
    {
        try {
            $admin = new User();
            $admin->setScenario('installation');
            $admin->username = 'admin';
            $admin->email    = 'podium_admin@podium.net';
            $admin->status   = User::STATUS_ACTIVE;
            $admin->role     = User::ROLE_ADMIN;
            $admin->generateAuthKey();
            $admin->setPassword('admin');
            if ($admin->save()) {

                $this->authManager->assign($this->authManager->getRole('admin'), $admin->getId());

                return $this->outputSuccess(Yii::t('podium/flash', 'Administrator account has been created.') .
                                ' ' . Html::tag('strong', Yii::t('podium/flash', 'Login') . ':') .
                                ' ' . Html::tag('kbd', 'admin') .
                                ' ' . Html::tag('strong', Yii::t('podium/flash', 'Password') . ':') .
                                ' ' . Html::tag('kbd', 'admin'));
            }
            else {
                $this->setError(true);
                return $this->outputDanger(Yii::t('podium/flash', 'Error during account creating') . ': ' .
                                Html::tag('pre', VarDumper::dumpAsString($admin->getErrors())));
            }
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during account creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Adds Config default settings.
     * @return string result message.
     */
    protected function _addConfig()
    {
        try {
            $this->db->createCommand()->batchInsert('{{%podium_config}}', ['name', 'value'], [
                    ['name', 'Podium'], 
                    ['version', '0.1'], 
                    ['hot_minimum', '20'], 
                    ['members_visible', '1'],
                    ['from_email', 'no-reply@change.me'],
                    ['from_name', 'Podium'],
                    ['max_attempts', '5'],
                    ['password_reset_token_expire', '86400'],
                    ['email_token_expire', '86400'],
                    ['activation_token_expire', '259200'],
                    ['meta_keywords', 'yii2, forum, podium'],
                    ['meta_description', 'Podium - Yii 2 Forum Module'],
                ])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Config default settings have been added.'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during settings adding') . ': ' . Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Adds permission rules.
     * @return string result message.
     */
    protected function _addRules()
    {
        try {
            $viewThread = $this->authManager->createPermission('viewThread');
            $viewThread->description = 'View thread';
            $this->authManager->add($viewThread);

            $viewForum = $this->authManager->createPermission('viewForum');
            $viewForum->description = 'View forum';
            $this->authManager->add($viewForum);

            $createThread = $this->authManager->createPermission('createThread');
            $createThread->description = 'Create thread';
            $this->authManager->add($createThread);

            $createPost = $this->authManager->createPermission('createPost');
            $createPost->description = 'Create post';
            $this->authManager->add($createPost);

            $moderatorRule = new ModeratorRule;
            $this->authManager->add($moderatorRule);

            $updatePost = $this->authManager->createPermission('updatePost');
            $updatePost->description = 'Update post';
            $updatePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($updatePost);

            $authorRule = new AuthorRule;
            $this->authManager->add($authorRule);

            $updateOwnPost = $this->authManager->createPermission('updateOwnPost');
            $updateOwnPost->description = 'Update own post';
            $updateOwnPost->ruleName    = $authorRule->name;
            $this->authManager->add($updateOwnPost);
            $this->authManager->addChild($updateOwnPost, $updatePost);

            $deletePost = $this->authManager->createPermission('deletePost');
            $deletePost->description = 'Delete post';
            $deletePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($deletePost);

            $deleteOwnPost = $this->authManager->createPermission('deleteOwnPost');
            $deleteOwnPost->description = 'Delete own post';
            $deleteOwnPost->ruleName    = $authorRule->name;
            $this->authManager->add($deleteOwnPost);
            $this->authManager->addChild($deleteOwnPost, $deletePost);
            
            $user = $this->authManager->createRole('user');
            $this->authManager->add($user);
            $this->authManager->addChild($user, $viewThread);
            $this->authManager->addChild($user, $viewForum);
            $this->authManager->addChild($user, $createThread);
            $this->authManager->addChild($user, $createPost);
            $this->authManager->addChild($user, $updateOwnPost);
            $this->authManager->addChild($user, $deleteOwnPost);

            $updateThread = $this->authManager->createPermission('updateThread');
            $updateThread->description = 'Update thread';
            $updateThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($updateThread);
            
            $deleteThread = $this->authManager->createPermission('deleteThread');
            $deleteThread->description = 'Delete thread';
            $deleteThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($deleteThread);
            
            $pinThread = $this->authManager->createPermission('pinThread');
            $pinThread->description = 'Pin thread';
            $pinThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($pinThread);
            
            $lockThread = $this->authManager->createPermission('lockThread');
            $lockThread->description = 'Lock thread';
            $lockThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($lockThread);

            $moveThread = $this->authManager->createPermission('moveThread');
            $moveThread->description = 'Move thread';
            $moveThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($moveThread);

            $movePost = $this->authManager->createPermission('movePost');
            $movePost->description = 'Move post';
            $movePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($movePost);

            $banUser = $this->authManager->createPermission('banUser');
            $banUser->description = 'Ban user';
            $this->authManager->add($banUser);

            $moderator = $this->authManager->createRole('moderator');
            $this->authManager->add($moderator);
            $this->authManager->addChild($moderator, $updatePost);
            $this->authManager->addChild($moderator, $updateThread);
            $this->authManager->addChild($moderator, $deletePost);
            $this->authManager->addChild($moderator, $deleteThread);
            $this->authManager->addChild($moderator, $pinThread);
            $this->authManager->addChild($moderator, $lockThread);
            $this->authManager->addChild($moderator, $moveThread);
            $this->authManager->addChild($moderator, $movePost);
            $this->authManager->addChild($moderator, $banUser);
            $this->authManager->addChild($moderator, $user);

            $createForum = $this->authManager->createPermission('createForum');
            $createForum->description = 'Create forum';
            $this->authManager->add($createForum);

            $updateForum = $this->authManager->createPermission('updateForum');
            $updateForum->description = 'Update forum';
            $this->authManager->add($updateForum);

            $deleteForum = $this->authManager->createPermission('deleteForum');
            $deleteForum->description = 'Delete forum';
            $this->authManager->add($deleteForum);
            
            $createCategory = $this->authManager->createPermission('createCategory');
            $createCategory->description = 'Create category';
            $this->authManager->add($createCategory);

            $updateCategory = $this->authManager->createPermission('updateCategory');
            $updateCategory->description = 'Update category';
            $this->authManager->add($updateCategory);

            $deleteCategory = $this->authManager->createPermission('deleteCategory');
            $deleteCategory->description = 'Delete category';
            $this->authManager->add($deleteCategory);

            $settings = $this->authManager->createPermission('settings');
            $settings->description = 'Settings';
            $this->authManager->add($settings);

            $admin = $this->authManager->createRole('admin');
            $this->authManager->add($admin);
            $this->authManager->addChild($admin, $createForum);
            $this->authManager->addChild($admin, $updateForum);
            $this->authManager->addChild($admin, $deleteForum);
            $this->authManager->addChild($admin, $createCategory);
            $this->authManager->addChild($admin, $updateCategory);
            $this->authManager->addChild($admin, $deleteCategory);
            $this->authManager->addChild($admin, $settings);
            $this->authManager->addChild($admin, $moderator);

            return $this->outputSuccess(Yii::t('podium/flash', 'Access roles have been created.'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during access roles creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Proceeds multiple installation drops.
     */
    protected function _proceedDrops()
    {
        $drops   = array_reverse($this->steps());
        $results = '';
        $this->setError(false);
        foreach ($drops as $drop) {
            if (isset($drop['call']) && $drop['call'] == 'create') {
                $result = '';
                $this->setTable($drop['table']);
                $result .= call_user_func([$this, '_drop']);
                if ($result != '') {
                    $result .= '<br>';
                }
                $results .= $result;
                $this->setResult($results);
                if ($this->getError()) {
                    $this->setPercent(100);
                    break;
                }
            }
        }
    }
    
    /**
     * Proceeds next installation step.
     * @param array $data step data.
     * @throws Exception
     */
    protected function _proceedStep($data)
    {
        if (empty($data['table'])) {
            throw new Exception(Yii::t('podium/flash', 'Installation aborted! Database table name missing.'));
        }
        else {
            $this->setTable($data['table']);
            if (empty($data['call'])) {
                throw new Exception(Yii::t('podium/flash', 'Installation aborted! Action call missing.'));
            }
            else {
                $this->setError(false);
                switch ($data['call']) {
                    case 'create':
                        $result = call_user_func([$this, '_create'], $data);
                        break;
                    case 'index':
                        $result = call_user_func([$this, '_index'], $data);
                        break;
                    case 'foreign':
                        $result = call_user_func([$this, '_foreign'], $data);
                        break;
                    default:
                        $result = call_user_func([$this, '_' . $data['call']], $data);
                }
                
                $this->setResult($result);
                if ($this->getError()) {
                    $this->setPercent(100);
                }
            }
        }
    }

    /**
     * Starts next step of installation.
     * @param integer $step step number.
     * @param boolean $drop wheter to drop table prior to creating it.
     * @return array installation step result.
     */
    public function step($step, $drop = false)
    {
        $this->setTable('...');
        try {
            if (!isset(static::steps()[(int)$step])) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            elseif ($this->getNumberOfSteps() == 0) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the installation steps.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            else {
                $this->setPercent($this->getNumberOfSteps() == (int)$step + 1 ? 100 : floor(100 * ((int)$step + 1) / $this->getNumberOfSteps()));
                if ($drop) {
                    $this->_proceedDrops();    
                }
                else {
                    $this->_proceedStep(static::steps()[(int)$step]);
                }
            }
        }
        catch (Exception $e) {
            $this->setResult($this->outputDanger($e->getMessage()));
            $this->setError(true);
            $this->setPercent(100);
        }
        
        return [
            'table'   => $this->getTable(),
            'percent' => $this->getPercent(),
            'result'  => $this->getResult(),
            'error'   => $this->getError(),
        ];
    }
    
    /**
     * Installation steps.
     */
    public static function steps()
    {
        return [
            [
                'table'  => 'config',
                'call'   => 'create',
                'schema' => [
                    'name'  => Schema::TYPE_STRING . ' NOT NULL',
                    'value' => Schema::TYPE_STRING . ' NOT NULL',
                    'PRIMARY KEY (name)',
                ],
            ],
            [
                'table' => 'config',
                'call'  => 'addConfig',
            ],
            [
                'table'  => 'log',
                'call'   => 'create',
                'schema' => [
                    'id'       => Schema::TYPE_BIGPK,
                    'level'    => Schema::TYPE_INTEGER,
                    'category' => Schema::TYPE_STRING,
                    'log_time' => Schema::TYPE_DOUBLE,
                    'prefix'   => Schema::TYPE_TEXT,
                    'message'  => Schema::TYPE_TEXT,
                    'model'    => Schema::TYPE_INTEGER,
                    'blame'    => Schema::TYPE_INTEGER,
                ],
            ],
            [
                'table' => 'log',
                'call'  => 'index',
                'name'  => 'level',
                'cols'  => ['level'],
            ],
            [
                'table' => 'log',
                'call'  => 'index',
                'name'  => 'category',
                'cols'  => ['category'],
            ],
            [
                'table' => 'log',
                'call'  => 'index',
                'name'  => 'model',
                'cols'  => ['model'],
            ],
            [
                'table' => 'log',
                'call'  => 'index',
                'name'  => 'blame',
                'cols'  => ['blame'],
            ],
            [
                'table'  => 'category',
                'call'   => 'create',
                'schema' => [
                    'id'          => Schema::TYPE_PK,
                    'name'        => Schema::TYPE_STRING . ' NOT NULL',
                    'slug'        => Schema::TYPE_STRING . ' NOT NULL',
                    'keywords'    => Schema::TYPE_STRING,
                    'description' => Schema::TYPE_STRING,
                    'visible'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'sort'        => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'created_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'category',
                'call'  => 'index',
                'name'  => 'sort',
                'cols'  => ['sort', 'id'],
            ],
            [
                'table' => 'category',
                'call'  => 'index',
                'name'  => 'name',
                'cols'  => ['name'],
            ],
            [
                'table' => 'category',
                'call'  => 'index',
                'name'  => 'display',
                'cols'  => ['id', 'slug'],
            ],
            [
                'table' => 'category',
                'call'  => 'index',
                'name'  => 'display_guest',
                'cols'  => ['id', 'slug', 'visible'],
            ],
            [
                'table'  => 'forum',
                'call'   => 'create',
                'schema' => [
                    'id'          => Schema::TYPE_PK,
                    'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'name'        => Schema::TYPE_STRING . ' NOT NULL',
                    'sub'         => Schema::TYPE_STRING,
                    'slug'        => Schema::TYPE_STRING . ' NOT NULL',
                    'keywords'    => Schema::TYPE_STRING,
                    'description' => Schema::TYPE_STRING,
                    'visible'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'sort'        => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'threads'     => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'posts'       => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'created_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'forum',
                'call'  => 'index',
                'name'  => 'sort',
                'cols'  => ['sort', 'id'],
            ],
            [
                'table' => 'forum',
                'call'  => 'index',
                'name'  => 'name',
                'cols'  => ['name'],
            ],
            [
                'table' => 'forum',
                'call'  => 'index',
                'name'  => 'display',
                'cols'  => ['id', 'category_id'],
            ],
            [
                'table' => 'forum',
                'call'  => 'index',
                'name'  => 'display_slug',
                'cols'  => ['id', 'category_id', 'slug'],
            ],
            [
                'table' => 'forum',
                'call'  => 'index',
                'name'  => 'display_guest_slug',
                'cols'  => ['id', 'category_id', 'slug', 'visible'],
            ],
            [
                'table'  => 'forum',
                'call'   => 'foreign',
                'key'    => 'category_id',
                'ref'    => 'category',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'thread',
                'call'   => 'create',
                'schema' => [
                    'id'             => Schema::TYPE_PK,
                    'name'           => Schema::TYPE_STRING . ' NOT NULL',
                    'slug'           => Schema::TYPE_STRING . ' NOT NULL',
                    'category_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'forum_id'       => Schema::TYPE_INTEGER . ' NOT NULL',
                    'author_id'      => Schema::TYPE_INTEGER . ' NOT NULL',
                    'pinned'         => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'locked'         => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'posts'          => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'views'          => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'created_at'     => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'     => Schema::TYPE_INTEGER . ' NOT NULL',
                    'new_post_at'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'edited_post_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'name',
                'cols'  => ['name'],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'created_at',
                'cols'  => ['created_at'],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'display',
                'cols'  => ['id', 'category_id', 'forum_id'],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'display_slug',
                'cols'  => ['id', 'category_id', 'forum_id', 'slug'],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'sort',
                'cols'  => ['pinned', 'updated_at', 'id'],
            ],
            [
                'table' => 'thread',
                'call'  => 'index',
                'name'  => 'sort_author',
                'cols'  => ['updated_at', 'id'],
            ],
            [
                'table'  => 'thread',
                'call'   => 'foreign',
                'key'    => 'category_id',
                'ref'    => 'category',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'thread',
                'call'   => 'foreign',
                'key'    => 'forum_id',
                'ref'    => 'forum',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'post',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'content'    => Schema::TYPE_TEXT . ' NOT NULL',
                    'thread_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'forum_id'   => Schema::TYPE_INTEGER . ' NOT NULL',
                    'author_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'edited'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'likes'      => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'dislikes'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'edited_at'  => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                ],
            ],
            [
                'table' => 'post',
                'call'  => 'index',
                'name'  => 'updated_at',
                'cols'  => ['updated_at'],
            ],
            [
                'table' => 'post',
                'call'  => 'index',
                'name'  => 'created_at',
                'cols'  => ['created_at'],
            ],
            [
                'table' => 'post',
                'call'  => 'index',
                'name'  => 'edited_at',
                'cols'  => ['edited_at'],
            ],
            [
                'table' => 'post',
                'call'  => 'index',
                'name'  => 'identify',
                'cols'  => ['id', 'thread_id', 'forum_id'],
            ],
            [
                'table'  => 'post',
                'call'   => 'foreign',
                'key'    => 'thread_id',
                'ref'    => 'thread',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'post',
                'call'   => 'foreign',
                'key'    => 'forum_id',
                'ref'    => 'forum',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'vocabulary',
                'call'   => 'create',
                'schema' => [
                    'id'   => Schema::TYPE_PK,
                    'word' => Schema::TYPE_STRING . ' NOT NULL',
                ],
            ],
            [
                'table' => 'vocabulary',
                'call'  => 'index',
                'name'  => 'word',
                'cols'  => ['word'],
            ],
            [
                'table'  => 'vocabulary_junction',
                'call'   => 'create',
                'schema' => [
                    'id'      => Schema::TYPE_PK,
                    'word_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'post_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'vocabulary_junction',
                'call'   => 'foreign',
                'key'    => 'word_id',
                'ref'    => 'vocabulary',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'vocabulary_junction',
                'call'   => 'foreign',
                'key'    => 'post_id',
                'ref'    => 'post',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'message',
                'call'   => 'create',
                'schema' => [
                    'id'              => Schema::TYPE_PK,
                    'sender_id'       => Schema::TYPE_INTEGER . ' NOT NULL',
                    'receiver_id'     => Schema::TYPE_INTEGER . ' NOT NULL',
                    'topic'           => Schema::TYPE_STRING . ' NOT NULL',
                    'content'         => Schema::TYPE_TEXT . ' NOT NULL',
                    'sender_status'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'receiver_status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'replyto'         => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'created_at'      => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'      => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'message',
                'call'  => 'index',
                'name'  => 'sender_id',
                'cols'  => ['sender_id'],
            ],
            [
                'table' => 'message',
                'call'  => 'index',
                'name'  => 'receiver_id',
                'cols'  => ['receiver_id'],
            ],
            [
                'table' => 'message',
                'call'  => 'index',
                'name'  => 'topic',
                'cols'  => ['topic'],
            ],
            [
                'table' => 'message',
                'call'  => 'index',
                'name'  => 'inbox',
                'cols'  => ['receiver_id', 'receiver_status'],
            ],
            [
                'table' => 'message',
                'call'  => 'index',
                'name'  => 'sent',
                'cols'  => ['sender_id', 'sender_status'],
            ],
            [
                'table'  => 'auth_rule',
                'call'   => 'create',
                'schema' => [
                    'name'       => Schema::TYPE_STRING . '(64) NOT NULL',
                    'data'       => Schema::TYPE_TEXT,
                    'created_at' => Schema::TYPE_INTEGER,
                    'updated_at' => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (name)',
                ],
            ],
            [
                'table'  => 'auth_item',
                'call'   => 'create',
                'schema' => [
                    'name'        => Schema::TYPE_STRING . '(64) NOT NULL',
                    'type'        => Schema::TYPE_INTEGER . ' NOT NULL',
                    'description' => Schema::TYPE_TEXT,
                    'rule_name'   => Schema::TYPE_STRING . '(64)',
                    'data'        => Schema::TYPE_TEXT,
                    'created_at'  => Schema::TYPE_INTEGER,
                    'updated_at'  => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (name)',
                ],
            ],
            [
                'table'  => 'auth_item',
                'call'   => 'foreign',
                'key'    => 'rule_name',
                'ref'    => 'auth_rule',
                'col'    => 'name',
                'delete' => 'SET NULL',
                'update' => 'CASCADE',
            ],
            [
                'table' => 'auth_item',
                'call'  => 'index',
                'name'  => 'type',
                'cols'  => ['type'],
            ],
            [
                'table'  => 'auth_item_child',
                'call'   => 'create',
                'schema' => [
                    'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
                    'child'  => Schema::TYPE_STRING . '(64) NOT NULL',
                    'PRIMARY KEY (parent, child)',
                ],
            ],
            [
                'table'  => 'auth_item_child',
                'call'   => 'foreign',
                'key'    => 'parent',
                'ref'    => 'auth_item',
                'col'    => 'name',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'auth_item_child',
                'call'   => 'foreign',
                'key'    => 'child',
                'ref'    => 'auth_item',
                'col'    => 'name',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'auth_assignment',
                'call'   => 'create',
                'schema' => [
                    'item_name'  => Schema::TYPE_STRING . '(64) NOT NULL',
                    'user_id'    => Schema::TYPE_STRING . '(64) NOT NULL',
                    'created_at' => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (item_name, user_id)',
                ],
            ],
            [
                'table'  => 'auth_assignment',
                'call'   => 'foreign',
                'key'    => 'item_name',
                'ref'    => 'auth_item',
                'col'    => 'name',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table' => 'auth_rule',
                'call'  => 'addRules',
            ],
            [
                'table'  => 'user',
                'call'   => 'create',
                'schema' => [
                    'id'                   => Schema::TYPE_PK,
                    'username'             => Schema::TYPE_STRING . ' NOT NULL',
                    'slug'                 => Schema::TYPE_STRING . ' NOT NULL',
                    'auth_key'             => Schema::TYPE_STRING . '(32) NOT NULL',
                    'password_hash'        => Schema::TYPE_STRING . ' NOT NULL',
                    'password_reset_token' => Schema::TYPE_STRING,
                    'activation_token'     => Schema::TYPE_STRING,
                    'email_token'          => Schema::TYPE_STRING,
                    'email'                => Schema::TYPE_STRING . ' NOT NULL',
                    'new_email'            => Schema::TYPE_STRING,
                    'anonymous'            => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'status'               => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'role'                 => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'timezone'             => Schema::TYPE_STRING . '(45)',
                    'created_at'           => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'           => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'username',
                'cols'  => ['username'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'status',
                'cols'  => ['status'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'role',
                'cols'  => ['role'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'email',
                'cols'  => ['email'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'mod',
                'cols'  => ['status', 'role'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'find_email',
                'cols'  => ['status', 'email'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'find_username',
                'cols'  => ['status', 'username'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'password_reset_token',
                'cols'  => ['password_reset_token'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'activation_token',
                'cols'  => ['activation_token'],
            ],
            [
                'table' => 'user',
                'call'  => 'index',
                'name'  => 'email_token',
                'cols'  => ['email_token'],
            ],
            [
                'table'  => 'user_meta',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'location'   => Schema::TYPE_STRING . '(32) NOT NULL',
                    'signature'  => Schema::TYPE_STRING . ' NOT NULL',
                    'gravatar'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'avatar'     => Schema::TYPE_STRING,
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'user_meta',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'user_ignore',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'ignored_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'user_ignore',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'user_ignore',
                'call'   => 'foreign',
                'key'    => 'ignored_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'user_activity',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER,
                    'username'   => Schema::TYPE_STRING,
                    'user_slug'  => Schema::TYPE_STRING,
                    'user_role'  => Schema::TYPE_INTEGER,
                    'url'        => Schema::TYPE_STRING . ' NOT NULL',
                    'ip'         => Schema::TYPE_STRING . '(15)',
                    'anonymous'  => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'user_activity',
                'call'  => 'index',
                'name'  => 'updated_at',
                'cols'  => ['updated_at'],
            ],
            [
                'table' => 'user_activity',
                'call'  => 'index',
                'name'  => 'members',
                'cols'  => ['updated_at', 'user_id', 'anonymous'],
            ],
            [
                'table' => 'user_activity',
                'call'  => 'index',
                'name'  => 'guests',
                'cols'  => ['updated_at', 'user_id'],
            ],
            [
                'table'  => 'user_activity',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'email',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER,
                    'email'      => Schema::TYPE_STRING . ' NOT NULL',
                    'subject'    => Schema::TYPE_TEXT . ' NOT NULL',
                    'content'    => Schema::TYPE_TEXT . ' NOT NULL',
                    'status'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'attempt'    => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table' => 'email',
                'call'  => 'index',
                'name'  => 'status',
                'cols'  => ['status'],
            ],
            [
                'table'  => 'email',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'thread_view',
                'call'   => 'create',
                'schema' => [
                    'id'               => Schema::TYPE_PK,
                    'user_id'          => Schema::TYPE_INTEGER . ' NOT NULL',
                    'thread_id'        => Schema::TYPE_INTEGER . ' NOT NULL',
                    'new_last_seen'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'edited_last_seen' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'thread_view',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'thread_view',
                'call'   => 'foreign',
                'key'    => 'thread_id',
                'ref'    => 'thread',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'post_thumb',
                'call'   => 'create',
                'schema' => [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'post_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'thumb'      => Schema::TYPE_SMALLINT . ' NOT NULL',
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'post_thumb',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'post_thumb',
                'call'   => 'foreign',
                'key'    => 'post_id',
                'ref'    => 'post',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'   => 'moderator',
                'call'    => 'create',
                'schema' => [
                    'id'       => Schema::TYPE_PK,
                    'user_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'forum_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
            ],
            [
                'table'  => 'moderator',
                'call'   => 'foreign',
                'key'    => 'user_id',
                'ref'    => 'user',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table'  => 'moderator',
                'call'   => 'foreign',
                'key'    => 'forum_id',
                'ref'    => 'forum',
                'col'    => 'id',
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ],
            [
                'table' => 'user',
                'call'  => 'addAdmin',
            ],
        ];
    }
}