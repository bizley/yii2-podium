<?php

namespace bizley\podium\components;

use Yii;
use yii\base\Component;
use yii\di\Instance;
use yii\db\Schema;
use yii\db\Connection;
use yii\helpers\VarDumper;
use yii\helpers\Html;
use yii\rbac\DbManager;
use bizley\podium\models\User;
use bizley\podium\rbac\AuthorRule;
use bizley\podium\rbac\ModeratorRule;

/**
 * @property Connection $db Database connection
 */
class Installation extends Component
{

    public $db               = 'db';
    public $authManager      = 'authManager';
    protected $_errors       = false;
    protected $_prefix       = 'podium_';
    protected $_tableOptions = null;
    protected $_tables       = [
        [
            'table'   => 'config',
            'call'    => 'createConfig',
            'percent' => 5
        ],
        [
            'table'   => 'message',
            'call'    => 'createMessage',
            'percent' => 10
        ],
        [
            'table'   => 'message',
            'call'    => 'createMessageSenderIndex',
            'percent' => 15
        ],
        [
            'table'   => 'message',
            'call'    => 'createMessageReceiverIndex',
            'percent' => 20
        ],
        [
            'table'   => 'auth_rule',
            'call'    => 'createAuthRule',
            'percent' => 25
        ],
        [
            'table'   => 'auth_item',
            'call'    => 'createAuthItem',
            'percent' => 30
        ],
        [
            'table'   => 'auth_item',
            'call'    => 'createAuthItemIndex',
            'percent' => 35
        ],
        [
            'table'   => 'auth_item_child',
            'call'    => 'createAuthItemChild',
            'percent' => 40
        ],
        [
            'table'   => 'auth_assignment',
            'call'    => 'createAuthAssignment',
            'percent' => 45
        ],
        [
            'table'   => 'auth_rule',
            'call'    => 'addRules',
            'percent' => 50
        ],
        [
            'table'   => 'user',
            'call'    => 'createUser',
            'percent' => 55
        ],
        [
            'table'   => 'user_meta',
            'call'    => 'createUserMeta',
            'percent' => 60
        ],
        [
            'table'   => 'user',
            'call'    => 'addAdmin',
            'percent' => 100
        ],
    ];

    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->db->driverName === 'mysql') {
            $this->_tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->authManager = Instance::ensure($this->authManager, DbManager::className());
    }

    public static function check()
    {
        try {
            (new User())->getTableSchema();
            return true;
        } catch (\Exception $e) {
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }

        return false;
    }

    public function step($step)
    {
        $proceed = $this->{'_' . $this->_tables[$step]['call']}('{{%' . $this->_prefix . $this->_tables[$step]['table'] . '}}');

        return [
            'table'   => $this->_prefix . $this->_tables[$step]['table'],
            'percent' => $this->_tables[$step]['percent'],
            'result'  => $proceed,
            'error'   => $this->_errors
        ];
    }

    protected function _createMessage($name)
    {
        return $this->_createTable($name, [
                    'id'              => Schema::TYPE_PK,
                    'sender'          => Schema::TYPE_INTEGER . ' NOT NULL',
                    'receiver'        => Schema::TYPE_INTEGER . ' NOT NULL',
                    'topic'           => Schema::TYPE_STRING . ' NOT NULL',
                    'content'         => Schema::TYPE_TEXT . ' NOT NULL',
                    'sender_status'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'receiver_status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'created_at'      => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'      => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
    }
    
    protected function _createMessageSenderIndex($name)
    {
        return $this->_createIndex('idx-message-sender', $name, 'sender');
    }
    
    protected function _createMessageReceiverIndex($name)
    {
        return $this->_createIndex('idx-message-receiver', $name, 'receiver');
    }

    protected function _createUser($name)
    {
        return $this->_createTable($name, [
                    'id'                   => Schema::TYPE_PK,
                    'username'             => Schema::TYPE_STRING . ' NOT NULL',
                    'auth_key'             => Schema::TYPE_STRING . '(32) NOT NULL',
                    'password_hash'        => Schema::TYPE_STRING . ' NOT NULL',
                    'password_reset_token' => Schema::TYPE_STRING,
                    'activation_token'     => Schema::TYPE_STRING,
                    'email_token'          => Schema::TYPE_STRING,
                    'email'                => Schema::TYPE_STRING . ' NOT NULL',
                    'new_email'            => Schema::TYPE_STRING,
                    'status'               => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'role'                 => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                    'created_at'           => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at'           => Schema::TYPE_INTEGER . ' NOT NULL',
        ]);
    }

    protected function _createUserMeta($name)
    {
        return $this->_createTable($name, [
                    'id'         => Schema::TYPE_PK,
                    'user_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                    'location'   => Schema::TYPE_STRING . '(32) NOT NULL',
                    'signature'  => Schema::TYPE_STRING . ' NOT NULL',
                    'gravatar'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                    'avatar'     => Schema::TYPE_STRING,
                    'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                    'FOREIGN KEY (user_id) REFERENCES {{%podium_user}} (id) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);
    }

    protected function _createAuthRule($name)
    {
        return $this->_createTable($name, [
                    'name'       => Schema::TYPE_STRING . '(64) NOT NULL',
                    'data'       => Schema::TYPE_TEXT,
                    'created_at' => Schema::TYPE_INTEGER,
                    'updated_at' => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (name)',
        ]);
    }

    protected function _createAuthItem($name)
    {
        return $this->_createTable($name, [
                    'name'        => Schema::TYPE_STRING . '(64) NOT NULL',
                    'type'        => Schema::TYPE_INTEGER . ' NOT NULL',
                    'description' => Schema::TYPE_TEXT,
                    'rule_name'   => Schema::TYPE_STRING . '(64)',
                    'data'        => Schema::TYPE_TEXT,
                    'created_at'  => Schema::TYPE_INTEGER,
                    'updated_at'  => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (name)',
                    'FOREIGN KEY (rule_name) REFERENCES {{%podium_auth_rule}} (name) ON DELETE SET NULL ON UPDATE CASCADE',
        ]);
    }

    protected function _createAuthItemIndex($name)
    {
        return $this->_createIndex('idx-auth_item-type', $name, 'type');
    }

    protected function _createAuthItemChild($name)
    {
        return $this->_createTable($name, [
                    'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
                    'child'  => Schema::TYPE_STRING . '(64) NOT NULL',
                    'PRIMARY KEY (parent, child)',
                    'FOREIGN KEY (parent) REFERENCES {{%podium_auth_item}} (name) ON DELETE CASCADE ON UPDATE CASCADE',
                    'FOREIGN KEY (child) REFERENCES {{%podium_auth_item}} (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);
    }

    protected function _createConfig($name)
    {
        return $this->_createTable($name, [
                    'name'  => Schema::TYPE_STRING . ' NOT NULL',
                    'value' => Schema::TYPE_STRING . ' NOT NULL',
                    'PRIMARY KEY (name)',
        ]);
    }

    protected function _createAuthAssignment($name)
    {
        return $this->_createTable($name, [
                    'item_name'  => Schema::TYPE_STRING . '(64) NOT NULL',
                    'user_id'    => Schema::TYPE_STRING . '(64) NOT NULL',
                    'created_at' => Schema::TYPE_INTEGER,
                    'PRIMARY KEY (item_name, user_id)',
                    'FOREIGN KEY (item_name) REFERENCES {{%podium_auth_item}} (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ]);
    }

    protected function _createTable($name, $columns)
    {
        try {
            $this->db->createCommand()->createTable($name, $columns, $this->_tableOptions)->execute();
            return $this->_outputSuccess(Yii::t('podium/flash', 'Table has been created'));
        } catch (\Exception $e) {
            $this->_errors = true;
            return $this->_outputDanger(Yii::t('podium/flash', 'Error during table creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    protected function _createIndex($index, $name, $columns)
    {
        try {
            $this->db->createCommand()->createIndex($index, $name, $columns)->execute();
            return $this->_outputSuccess(Yii::t('podium/flash', 'Table index has been added'));
        } catch (\Exception $e) {
            $this->_errors = true;
            return $this->_outputDanger(Yii::t('podium/flash', 'Error during table index adding') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

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

                return $this->_outputSuccess(Yii::t('podium/flash', 'Administrator account has been created.') .
                                ' ' . Html::tag('strong', Yii::t('podium/flash', 'Login') . ':') .
                                ' ' . Html::tag('kbd', 'admin') .
                                ' ' . Html::tag('strong', Yii::t('podium/flash', 'Password') . ':') .
                                ' ' . Html::tag('kbd', 'admin'));
            }
            else {
                $this->_errors = true;
                return $this->_outputDanger(Yii::t('podium/flash', 'Error during account creating') . ': ' .
                                Html::tag('pre', VarDumper::dumpAsString($admin->getErrors())));
            }
        } catch (\Exception $e) {
            $this->_errors = true;
            return $this->_outputDanger(Yii::t('podium/flash', 'Error during account creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    protected function _outputSuccess($content)
    {
        return Html::tag('span', $content, ['class' => 'text-success']);
    }

    protected function _outputDanger($content)
    {
        return Html::tag('span', $content, ['class' => 'text-danger']);
    }

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

            $updateThread              = $this->authManager->createPermission('updateThread');
            $updateThread->description = 'Update thread';
            $updateThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($updateThread);

            $authorRule = new AuthorRule;
            $this->authManager->add($authorRule);

            $updateOwnThread              = $this->authManager->createPermission('updateOwnThread');
            $updateOwnThread->description = 'Update own thread';
            $updateOwnThread->ruleName    = $authorRule->name;
            $this->authManager->add($updateOwnThread);
            $this->authManager->addChild($updateOwnThread, $updateThread);

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

            $pinThread              = $this->authManager->createPermission('pinThread');
            $pinThread->description = 'Pin thread';
            $pinThread->ruleName    = $moderatorRule->name;
            $this->authManager->add($pinThread);

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
            $this->authManager->addChild($moderator, $pinThread);
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

            $settings              = $this->authManager->createPermission('settings');
            $settings->description = 'Settings';
            $this->authManager->add($settings);

            $admin = $this->authManager->createRole('admin');
            $this->authManager->add($admin);
            $this->authManager->addChild($admin, $createForum);
            $this->authManager->addChild($admin, $updateForum);
            $this->authManager->addChild($admin, $deleteForum);
            $this->authManager->addChild($admin, $settings);
            $this->authManager->addChild($admin, $moderator);

            return $this->_outputSuccess(Yii::t('podium/flash', 'Access roles have been created.'));
        } catch (\Exception $e) {
            $this->_errors = true;
            return $this->_outputDanger(Yii::t('podium/flash', 'Error during access roles creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

}
