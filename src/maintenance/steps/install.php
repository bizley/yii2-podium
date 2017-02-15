<?php

/**
 * Podium installation steps.
 * v0.7
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 */

use yii\db\Schema;

return [
    [
        'table' => 'log',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_BIGPK,
                'level' => Schema::TYPE_INTEGER,
                'category' => Schema::TYPE_STRING,
                'log_time' => Schema::TYPE_DOUBLE,
                'ip' => Schema::TYPE_STRING . '(20)',
                'message' => Schema::TYPE_TEXT,
                'model' => Schema::TYPE_INTEGER,
                'user' => Schema::TYPE_INTEGER,
            ],
        ],
    ],
    [
        'table' => 'log',
        'call' => 'addIndex',
        'data' => [
            'name' => 'level',
            'cols' => ['level'],
        ],
    ],
    [
        'table' => 'log',
        'call' => 'addIndex',
        'data' => [
            'name' => 'category',
            'cols' => ['category'],
        ],
    ],
    [
        'table' => 'log',
        'call' => 'addIndex',
        'data' => [
            'name' => 'model',
            'cols' => ['model'],
        ],
    ],
    [
        'table' => 'log',
        'call' => 'addIndex',
        'data' => [
            'name' => 'user',
            'cols' => ['user'],
        ],
    ],
    [
        'table' => 'config',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'value' => Schema::TYPE_STRING . ' NOT NULL',
                'PRIMARY KEY (name)',
            ],
        ],
    ],
    [
        'table' => 'config',
        'call' => 'addConfig',
        'data' => []
    ],
    [
        'table' => 'category',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'slug' => Schema::TYPE_STRING . ' NOT NULL',
                'keywords' => Schema::TYPE_STRING,
                'description' => Schema::TYPE_STRING,
                'visible' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'sort' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'category',
        'call' => 'addIndex',
        'data' => [
            'name' => 'sort',
            'cols' => ['sort', 'id'],
        ],
    ],
    [
        'table' => 'category',
        'call' => 'addIndex',
        'data' => [
            'name' => 'name',
            'cols' => ['name'],
        ],
    ],
    [
        'table' => 'category',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display',
            'cols' => ['id', 'slug'],
        ],
    ],
    [
        'table' => 'category',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display_guest',
            'cols' => ['id', 'slug', 'visible'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'sub' => Schema::TYPE_STRING,
                'slug' => Schema::TYPE_STRING . ' NOT NULL',
                'keywords' => Schema::TYPE_STRING,
                'description' => Schema::TYPE_STRING,
                'visible' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'sort' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'threads' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'posts' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addIndex',
        'data' => [
            'name' => 'sort',
            'cols' => ['sort', 'id'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addIndex',
        'data' => [
            'name' => 'name',
            'cols' => ['name'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display',
            'cols' => ['id', 'category_id'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display_slug',
            'cols' => ['id', 'category_id', 'slug'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display_guest_slug',
            'cols' => ['id', 'category_id', 'slug', 'visible'],
        ],
    ],
    [
        'table' => 'forum',
        'call' => 'addForeign',
        'data' => [
            'key' => 'category_id',
            'ref' => 'category',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'slug' => Schema::TYPE_STRING . ' NOT NULL',
                'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'forum_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'author_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'pinned' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'locked' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'posts' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'views' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'new_post_at' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'edited_post_at' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'name',
            'cols' => ['name'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'created_at',
            'cols' => ['created_at'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display',
            'cols' => ['id', 'category_id', 'forum_id'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'display_slug',
            'cols' => ['id', 'category_id', 'forum_id', 'slug'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'sort',
            'cols' => ['pinned', 'updated_at', 'id'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addIndex',
        'data' => [
            'name' => 'sort_author',
            'cols' => ['updated_at', 'id'],
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addForeign',
        'data' => [
            'key' => 'category_id',
            'ref' => 'category',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'thread',
        'call' => 'addForeign',
        'data' => [
            'key' => 'forum_id',
            'ref' => 'forum',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'post',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'content' => Schema::TYPE_TEXT . ' NOT NULL',
                'thread_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'forum_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'author_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'edited' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'likes' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'dislikes' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'edited_at' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addIndex',
        'data' => [
            'name' => 'updated_at',
            'cols' => ['updated_at'],
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addIndex',
        'data' => [
            'name' => 'created_at',
            'cols' => ['created_at'],
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addIndex',
        'data' => [
            'name' => 'edited_at',
            'cols' => ['edited_at'],
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addIndex',
        'data' => [
            'name' => 'identify',
            'cols' => ['id', 'thread_id', 'forum_id'],
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addForeign',
        'data' => [
            'key' => 'thread_id',
            'ref' => 'thread',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'post',
        'call' => 'addForeign',
        'data' => [
            'key' => 'forum_id',
            'ref' => 'forum',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'vocabulary',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'word' => Schema::TYPE_STRING . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'vocabulary',
        'call' => 'addIndex',
        'data' => [
            'name' => 'word',
            'cols' => ['word'],
        ],
    ],
    [
        'table' => 'vocabulary_junction',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'word_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'post_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'vocabulary_junction',
        'call' => 'addForeign',
        'data' => [
            'key' => 'word_id',
            'ref' => 'vocabulary',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'vocabulary_junction',
        'call' => 'addForeign',
        'data' => [
            'key' => 'post_id',
            'ref' => 'post',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'auth_rule',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'name' => Schema::TYPE_STRING . '(64) NOT NULL',
                'data' => Schema::TYPE_TEXT,
                'created_at' => Schema::TYPE_INTEGER,
                'updated_at' => Schema::TYPE_INTEGER,
                'PRIMARY KEY (name)',
            ],
        ],
    ],
    [
        'table' => 'auth_item',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'name' => Schema::TYPE_STRING . '(64) NOT NULL',
                'type' => Schema::TYPE_INTEGER . ' NOT NULL',
                'description' => Schema::TYPE_TEXT,
                'rule_name' => Schema::TYPE_STRING . '(64)',
                'data' => Schema::TYPE_TEXT,
                'created_at' => Schema::TYPE_INTEGER,
                'updated_at' => Schema::TYPE_INTEGER,
                'PRIMARY KEY (name)',
            ],
        ],
    ],
    [
        'table' => 'auth_item',
        'call' => 'addForeign',
        'data' => [
            'key' => 'rule_name',
            'ref' => 'auth_rule',
            'col' => 'name',
            'delete' => 'SET NULL',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'auth_item',
        'call' => 'addIndex',
        'data' => [
            'name' => 'type',
            'cols' => ['type'],
        ],
    ],
    [
        'table' => 'auth_item_child',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
                'child' => Schema::TYPE_STRING . '(64) NOT NULL',
                'PRIMARY KEY (parent, child)',
            ],
        ],
    ],
    [
        'table' => 'auth_item_child',
        'call' => 'addForeign',
        'data' => [
            'key' => 'parent',
            'ref' => 'auth_item',
            'col' => 'name',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'auth_item_child',
        'call' => 'addForeign',
        'data' => [
            'key' => 'child',
            'ref' => 'auth_item',
            'col' => 'name',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'auth_assignment',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
                'user_id' => Schema::TYPE_STRING . '(64) NOT NULL',
                'created_at' => Schema::TYPE_INTEGER,
                'PRIMARY KEY (item_name, user_id)',
            ],
        ],
    ],
    [
        'table' => 'auth_assignment',
        'call' => 'addForeign',
        'data' => [
            'key' => 'item_name',
            'ref' => 'auth_item',
            'col' => 'name',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'auth_rule',
        'call' => 'addRules',
        'data' => [],
    ],
    [
        'table' => 'user',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'inherited_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'username' => Schema::TYPE_STRING,
                'slug' => Schema::TYPE_STRING . ' NOT NULL',
                'auth_key' => Schema::TYPE_STRING . '(32)',
                'password_hash' => Schema::TYPE_STRING,
                'password_reset_token' => Schema::TYPE_STRING,
                'activation_token' => Schema::TYPE_STRING,
                'email_token' => Schema::TYPE_STRING,
                'email' => Schema::TYPE_STRING,
                'new_email' => Schema::TYPE_STRING,
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'username',
            'cols' => ['username'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'status',
            'cols' => ['status'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'role',
            'cols' => ['role'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'email',
            'cols' => ['email'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'mod',
            'cols' => ['status', 'role'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'find_email',
            'cols' => ['status', 'email'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'find_username',
            'cols' => ['status', 'username'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'password_reset_token',
            'cols' => ['password_reset_token'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'activation_token',
            'cols' => ['activation_token'],
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addIndex',
        'data' => [
            'name' => 'email_token',
            'cols' => ['email_token'],
        ],
    ],
    [
        'table' => 'user_meta',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'location' => Schema::TYPE_STRING . '(32) NOT NULL',
                'signature' => Schema::TYPE_STRING . '(512) NOT NULL',
                'gravatar' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'avatar' => Schema::TYPE_STRING,
                'anonymous' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'timezone' => Schema::TYPE_STRING . '(45)',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'user_meta',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user_ignore',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'ignored_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'user_ignore',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user_ignore',
        'call' => 'addForeign',
        'data' => [
            'key' => 'ignored_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user_friend',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'friend_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'user_friend',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user_friend',
        'call' => 'addForeign',
        'data' => [
            'key' => 'friend_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user_activity',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER,
                'username' => Schema::TYPE_STRING,
                'user_slug' => Schema::TYPE_STRING,
                'user_role' => Schema::TYPE_INTEGER,
                'url' => Schema::TYPE_STRING . '(1024) NOT NULL',
                'ip' => Schema::TYPE_STRING . '(15)',
                'anonymous' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'user_activity',
        'call' => 'addIndex',
        'data' => [
            'name' => 'updated_at',
            'cols' => ['updated_at'],
        ],
    ],
    [
        'table' => 'user_activity',
        'call' => 'addIndex',
        'data' => [
            'name' => 'members',
            'cols' => ['updated_at', 'user_id', 'anonymous'],
        ],
    ],
    [
        'table' => 'user_activity',
        'call' => 'addIndex',
        'data' => [
            'name' => 'guests',
            'cols' => ['updated_at', 'user_id'],
        ],
    ],
    [
        'table' => 'user_activity',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'message',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'sender_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'topic' => Schema::TYPE_STRING . ' NOT NULL',
                'content' => Schema::TYPE_TEXT . ' NOT NULL',
                'sender_status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'replyto' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'message',
        'call' => 'addIndex',
        'data' => [
            'name' => 'topic',
            'cols' => ['topic'],
        ],
    ],
    [
        'table' => 'message',
        'call' => 'addIndex',
        'data' => [
            'name' => 'replyto',
            'cols' => ['replyto'],
        ],
    ],
    [
        'table' => 'message',
        'call' => 'addIndex',
        'data' => [
            'name' => 'sent',
            'cols' => ['sender_id', 'sender_status'],
        ],
    ],
    [
        'table' => 'message',
        'call' => 'addForeign',
        'data' => [
            'key' => 'sender_id',
            'ref' => 'user',
            'col' => 'id',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'message_receiver',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'message_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'receiver_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'receiver_status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'message_receiver',
        'call' => 'addIndex',
        'data' => [
            'name' => 'inbox',
            'cols' => ['receiver_id', 'receiver_status'],
        ],
    ],
    [
        'table' => 'message_receiver',
        'call' => 'addForeign',
        'data' => [
            'key' => 'message_id',
            'ref' => 'message',
            'col' => 'id',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'message_receiver',
        'call' => 'addForeign',
        'data' => [
            'key' => 'receiver_id',
            'ref' => 'user',
            'col' => 'id',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'email',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER,
                'email' => Schema::TYPE_STRING . ' NOT NULL',
                'subject' => Schema::TYPE_TEXT . ' NOT NULL',
                'content' => Schema::TYPE_TEXT . ' NOT NULL',
                'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'attempt' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'email',
        'call' => 'addIndex',
        'data' => [
            'name' => 'status',
            'cols' => ['status'],
        ],
    ],
    [
        'table' => 'email',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'thread_view',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'thread_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'new_last_seen' => Schema::TYPE_INTEGER . ' NOT NULL',
                'edited_last_seen' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'thread_view',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'thread_view',
        'call' => 'addForeign',
        'data' => [
            'key' => 'thread_id',
            'ref' => 'thread',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'post_thumb',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'post_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'thumb' => Schema::TYPE_SMALLINT . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'post_thumb',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'post_thumb',
        'call' => 'addForeign',
        'data' => [
            'key' => 'post_id',
            'ref' => 'post',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'subscription',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'thread_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'post_seen' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
            ],
        ],
    ],
    [
        'table' => 'subscription',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'subscription',
        'call' => 'addForeign',
        'data' => [
            'key' => 'thread_id',
            'ref' => 'thread',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'moderator',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'forum_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'moderator',
        'call' => 'addForeign',
        'data' => [
            'key' => 'user_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'moderator',
        'call' => 'addForeign',
        'data' => [
            'key' => 'forum_id',
            'ref' => 'forum',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'content',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'topic' => Schema::TYPE_STRING . ' NOT NULL',
                'content' => Schema::TYPE_TEXT . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'content',
        'call' => 'addContent',
        'data' => [],
    ],
    [
        'table' => 'poll',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'question' => Schema::TYPE_STRING . ' NOT NULL',
                'votes' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'hidden' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'end_at' => Schema::TYPE_INTEGER,
                'thread_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'author_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'poll',
        'call' => 'addForeign',
        'data' => [
            'key' => 'thread_id',
            'ref' => 'thread',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'poll',
        'call' => 'addForeign',
        'data' => [
            'key' => 'author_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'poll_answer',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'poll_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'answer' => Schema::TYPE_STRING . ' NOT NULL',
                'votes' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            ],
        ],
    ],
    [
        'table' => 'poll_answer',
        'call' => 'addForeign',
        'data' => [
            'key' => 'poll_id',
            'ref' => 'poll',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'poll_vote',
        'call' => 'createTable',
        'data' => [
            'schema' => [
                'id' => Schema::TYPE_PK,
                'poll_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'answer_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'caster_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
    ],
    [
        'table' => 'poll_vote',
        'call' => 'addForeign',
        'data' => [
            'key' => 'poll_id',
            'ref' => 'poll',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'poll_vote',
        'call' => 'addForeign',
        'data' => [
            'key' => 'answer_id',
            'ref' => 'poll_answer',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'poll_vote',
        'call' => 'addForeign',
        'data' => [
            'key' => 'caster_id',
            'ref' => 'user',
            'col' => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
    ],
    [
        'table' => 'user',
        'call' => 'addAdmin',
        'data' => [],
    ],
];