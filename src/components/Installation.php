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
use yii\base\Component;
use yii\db\Connection;
use yii\db\Schema;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\VarDumper;
use yii\rbac\DbManager;

/**
 * Podium Installation
 * Installation requires database connection to be configured first.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\DbManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Installation extends Component
{

    /**
     * @var DbManager authorization manager.
     */
    public $authManager = 'authManager';
    
    /**
     * @var Connection database connection.
     */
    public $db = 'db';

    /**
     * @var number of all steps
     */
    protected $_installationSteps;
    
    /**
     * @var boolean error flag.
     */
    protected $_error = false;
    
    protected $_table = '-';
    protected $_percent = 0;
    protected $_result;

    /**
     * @var string Podium database prefix.
     */
    protected $_prefix = 'podium_';

    /**
     * @var string additional SQL fragment that will be appended to the generated SQL.
     */
    protected $_tableOptions = null;

    /**
     * Adds Administrator account.
     * @return string result message.
     */
    protected function _addAdmin()
    {
        try {
            $admin           = new User();
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
                $this->_errors = true;
                return $this->outputDanger(Yii::t('podium/flash', 'Error during account creating') . ': ' .
                                Html::tag('pre', VarDumper::dumpAsString($admin->getErrors())));
            }
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->_errors = true;
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
                    ['version', '1.0'], 
                    ['hot_minimum', '20'], 
                    ['members_visible', '1'],
                    ['from_email', 'no-reply@podium-default.net'],
                    ['from_name', 'Podium'],
                    ['max_attempts', '5'],
                ])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Config default settings have been added.'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->_errors = true;
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
            $viewThread              = $this->authManager->createPermission('viewThread');
            $viewThread->description = 'View thread';
            $this->authManager->add($viewThread);

            $viewForum              = $this->authManager->createPermission('viewForum');
            $viewForum->description = 'View forum';
            $this->authManager->add($viewForum);

            $createThread              = $this->authManager->createPermission('createThread');
            $createThread->description = 'Create thread';
            $this->authManager->add($createThread);

            $createPost              = $this->authManager->createPermission('createPost');
            $createPost->description = 'Create post';
            $this->authManager->add($createPost);

            $moderatorRule = new ModeratorRule;
            $this->authManager->add($moderatorRule);

            $updatePost              = $this->authManager->createPermission('updatePost');
            $updatePost->description = 'Update post';
            $updatePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($updatePost);

            $authorRule = new AuthorRule;
            $this->authManager->add($authorRule);

            $updateOwnPost              = $this->authManager->createPermission('updateOwnPost');
            $updateOwnPost->description = 'Update own post';
            $updateOwnPost->ruleName    = $authorRule->name;
            $this->authManager->add($updateOwnPost);
            $this->authManager->addChild($updateOwnPost, $updatePost);

            $deletePost              = $this->authManager->createPermission('deletePost');
            $deletePost->description = 'Delete post';
            $deletePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($deletePost);

            $deleteOwnPost              = $this->authManager->createPermission('deleteOwnPost');
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

            $updateThread              = $this->authManager->createPermission('updateThread');
            $updateThread->description = 'Update thread';
            $updateThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($updateThread);
            
            $deleteThread              = $this->authManager->createPermission('deleteThread');
            $deleteThread->description = 'Delete thread';
            $deleteThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($deleteThread);
            
            $pinThread              = $this->authManager->createPermission('pinThread');
            $pinThread->description = 'Pin thread';
            $pinThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($pinThread);
            
            $lockThread              = $this->authManager->createPermission('lockThread');
            $lockThread->description = 'Lock thread';
            $lockThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($lockThread);

            $moveThread              = $this->authManager->createPermission('moveThread');
            $moveThread->description = 'Move thread';
            $moveThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($moveThread);

            $movePost              = $this->authManager->createPermission('movePost');
            $movePost->description = 'Move post';
            $movePost->ruleName    = $moderatorRule->name;
            $this->authManager->add($movePost);

            $banUser              = $this->authManager->createPermission('banUser');
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

            $createForum              = $this->authManager->createPermission('createForum');
            $createForum->description = 'Create forum';
            $this->authManager->add($createForum);

            $updateForum              = $this->authManager->createPermission('updateForum');
            $updateForum->description = 'Update forum';
            $this->authManager->add($updateForum);

            $deleteForum              = $this->authManager->createPermission('deleteForum');
            $deleteForum->description = 'Delete forum';
            $this->authManager->add($deleteForum);
            
            $createCategory              = $this->authManager->createPermission('createCategory');
            $createCategory->description = 'Create category';
            $this->authManager->add($createCategory);

            $updateCategory              = $this->authManager->createPermission('updateCategory');
            $updateCategory->description = 'Update category';
            $this->authManager->add($updateCategory);

            $deleteCategory              = $this->authManager->createPermission('deleteCategory');
            $deleteCategory->description = 'Delete category';
            $this->authManager->add($deleteCategory);

            $settings              = $this->authManager->createPermission('settings');
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
            $this->_errors = true;
            return $this->outputDanger(Yii::t('podium/flash', 'Error during access roles creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    protected function _count($array)
    {
        foreach ($array as $step) {
            $this->_count++;
            if (!empty($step['after'])) {
                $this->_count($step['after']);
            }
        }
    }
    
    /**
     * Creates database table index.
     * @param string $index index name.
     * @param string $name table name.
     * @param string|array table columns.
     * @return string result message.
     */
    protected function _createIndex($index, $name, $columns)
    {
        try {
            $this->db->createCommand()->createIndex($index, $name, $columns)->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index has been added'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->_errors = true;
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table index adding') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Creates database table.
     * @param string $name table name.
     * @param array $columns table columns.
     * @return string result message.
     */
    protected function _createTable($name, $columns)
    {
        try {
            $this->db->createCommand()->createTable($name, $columns, $this->_tableOptions)->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table has been created'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->_errors = true;
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Prepares error message.
     * @param string $content message content.
     * @return string prepared message.
     */
    public function outputDanger($content)
    {
        return Html::tag('span', $content, ['class' => 'text-danger']);
    }

    /**
     * Prepares success message.
     * @param string $content message content.
     * @return string prepared message.
     */
    public function outputSuccess($content)
    {
        return Html::tag('span', $content, ['class' => 'text-success']);
    }

    /**
     * Checks if User database table exists.
     * @return boolean wheter User database exists.
     */
    public static function check()
    {
        try {
            (new User())->getTableSchema();
            return true;
        }
        catch (Exception $e) {
            Yii::warning('Podium user database table not found - preparing for installation', __METHOD__);
        }

        return false;
    }

    public function getInstallationSteps()
    {
        if ($this->_installationSteps === null) {
            $this->_installationSteps = count(static::steps());
        }
        return $this->_installationSteps;
    }
    
    public function setInstallationSteps()
    {
        throw new Exception('Don\'t set installation steps counter directly!');
    }
    
    public function getError()
    {
        return $this->_error;
    }
    
    public function setError($value)
    {
        $this->_error = $value ? true : false;
    }
    
    public function getTableOptions()
    {
        return $this->_tableOptions;
    }
    
    public function setTableOptions($value)
    {
        $this->_tableOptions = $value;
    }
    
    public function getResult()
    {
        return $this->_result;
    }
    
    public function setResult($value)
    {
        $this->_result = $value;
    }
    
    public function getPercent()
    {
        return $this->_percent;
    }
    
    public function setPercent($value)
    {
        $this->_percent = (int)$value;
    }
    
    public function getTable()
    {
        return $this->_table;
    }
    
    public function setTable($value)
    {
        $this->_table = $value;
    }
    
    /**
     * Initialise component.
     */
    public function init()
    {
        parent::init();

        try {
            $this->db = Instance::ensure($this->db, Connection::className());
            if ($this->db->driverName === 'mysql') {
                $this->setTableOptions('CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
            }
            $this->authManager = Instance::ensure($this->authManager, DbManager::className());
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
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
        try {
            if (!isset(static::steps()[(int)$step])) {
                return [
                    'table'   => '-',
                    'percent' => 100,
                    'result'  => $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.')),
                    'error'   => true,
                ];
            }
            if ($this->getInstallationSteps() == 0) {
                return [
                    'table'   => '-',
                    'percent' => 100,
                    'result'  => $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the installation steps.')),
                    'error'   => true,
                ];
            }
            
            $percent = floor(100 * ($step + 1) / $this->getInstallationSteps());
            

            $install = static::steps();
            foreach ($this->getPath() as $nextLevel) {
                if (!isset($install[$nextLevel])) {
                    $this->setError(true);
                    $this->setTable(null);
                    $this->setPercent(100);
                    $this->setNext('stop');
                    $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.')));
                    break;
                }
                else {
                    $install = $install[$nextLevel];
                }
            }

            $this->setTable($install['table']);



            $proceed = $this->{'_' . $this->_steps[$step]['call']}('{{%' . $this->_prefix . $this->_steps[$step]['table'] . '}}');

            return [
                'table'   => $this->_prefix . $this->_steps[$step]['table'],
                'percent' => $this->_steps[$step]['percent'],
                'result'  => $proceed,
                'error'   => $this->_errors
            ];
            
        }
        catch (Exception $e) {
            $this->setResult($this->outputDanger($e->getMessage()));
        }
        
        return [
            'table'   => $this->getTable(),
            'percent' => $this->getPercent(),
            'result'  => $this->getResult(),
            'next'    => $this->getNext(),
            'error'   => $this->getErrors(),
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
                'call'  => 'add',
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
                    'id'         => Schema::TYPE_PK,
                    'name'       => Schema::TYPE_STRING . ' NOT NULL',
                    'slug'       => Schema::TYPE_STRING . ' NOT NULL',
                    'visible'    => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'sort'       => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
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
                    'visible'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'sort'        => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'threads'     => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'posts'       => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                    'created_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'  => Schema::TYPE_INTEGER . ' NOT NULL',
                ],
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