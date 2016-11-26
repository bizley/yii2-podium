<?php

/**
 * Podium installation steps.
 * v0.5
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 */

use yii\db\Schema;

return [
    '0.2' => [
        [
            'table'  => 'user_friend',
            'call'   => 'createTable',
            'schema' => [
                'id'        => Schema::TYPE_PK,
                'user_id'   => Schema::TYPE_INTEGER . ' NOT NULL',
                'friend_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
        [
            'table'  => 'user_friend',
            'call'   => 'addForeign',
            'key'    => 'user_id',
            'ref'    => 'user',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'  => 'user_friend',
            'call'   => 'addForeign',
            'key'    => 'friend_id',
            'ref'    => 'user',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table' => 'thread',
            'call'  => 'alterColumn',
            'col'   => 'new_post_at',
            'type'  => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
        ],
        [
            'table' => 'thread',
            'call'  => 'alterColumn',
            'col'   => 'edited_post_at',
            'type'  => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
        ],
        [
            'table' => 'log',
            'call'  => 'renameColumn',
            'col'   => 'prefix',
            'name'  => 'ip',
        ],
        [
            'table' => 'log',
            'call'  => 'alterColumn',
            'col'   => 'ip',
            'type'  => Schema::TYPE_STRING . '(20)',
        ],
        [
            'table' => 'log',
            'call'  => 'dropForeign',
            'name'  => 'blame',
        ],
        [
            'table' => 'log',
            'call'  => 'renameColumn',
            'col'   => 'blame',
            'name'  => 'user',
        ],
        [
            'table' => 'log',
            'call'  => 'addIndex',
            'name'  => 'user',
            'cols'  => ['user'],
        ],
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'merge_posts',
            'value' => '1'
        ],
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'version',
            'value' => '0.2'
        ]
    ],
    '0.3' => [
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'registration_off',
            'value' => '0'
        ],
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'version',
            'value' => '0.3'
        ]
    ],
    '0.4' => [
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'version',
            'value' => '0.4'
        ]
    ],
    '0.5' => [
        [
            'table' => 'user_activity',
            'call'  => 'dropIndex',
            'name'  => 'url',
        ],
        [
            'table'   => 'poll',
            'call'    => 'createTable',
            'schema' => [
                'id'         => Schema::TYPE_PK,
                'question'   => Schema::TYPE_STRING . ' NOT NULL',
                'votes'      => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
                'hidden'     => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
                'end_at'     => Schema::TYPE_INTEGER,
                'thread_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                'author_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
        [
            'table'  => 'poll',
            'call'   => 'addForeign',
            'key'    => 'thread_id',
            'ref'    => 'thread',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'  => 'poll',
            'call'   => 'addForeign',
            'key'    => 'author_id',
            'ref'    => 'user',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'   => 'poll_answer',
            'call'    => 'createTable',
            'schema' => [
                'id'      => Schema::TYPE_PK,
                'poll_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'answer'  => Schema::TYPE_STRING . ' NOT NULL',
                'votes'   => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            ],
        ],
        [
            'table'  => 'poll_answer',
            'call'   => 'addForeign',
            'key'    => 'poll_id',
            'ref'    => 'poll',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'   => 'poll_vote',
            'call'    => 'createTable',
            'schema' => [
                'id'         => Schema::TYPE_PK,
                'poll_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
                'answer_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                'caster_id'  => Schema::TYPE_INTEGER . ' NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        ],
        [
            'table'  => 'poll_vote',
            'call'   => 'addForeign',
            'key'    => 'poll_id',
            'ref'    => 'poll',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'  => 'poll_vote',
            'call'   => 'addForeign',
            'key'    => 'answer_id',
            'ref'    => 'poll_answer',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table'  => 'poll_vote',
            'call'   => 'addForeign',
            'key'    => 'caster_id',
            'ref'    => 'user',
            'col'    => 'id',
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ],
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'allow_polls',
            'value' => '1'
        ],
        [
            'table' => 'config',
            'call'  => 'updateValue',
            'name'  => 'version',
            'value' => '0.5'
        ]
    ],
];
