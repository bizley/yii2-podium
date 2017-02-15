<?php

/**
 * Podium installation steps.
 * v0.7
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 */

use yii\db\Schema;

return [
    '0.2' => [
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
            'table' => 'thread',
            'call' => 'alterColumn',
            'data' => [
                'col' => 'new_post_at',
                'type' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
        ],
        [
            'table' => 'thread',
            'call' => 'alterColumn',
            'data' => [
                'col' => 'edited_post_at',
                'type' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
        ],
        [
            'table' => 'log',
            'call' => 'renameColumn',
            'data' => [
                'col' => 'prefix',
                'name' => 'ip',
            ],
        ],
        [
            'table' => 'log',
            'call' => 'alterColumn',
            'data' => [
                'col' => 'ip',
                'type' => Schema::TYPE_STRING . '(20)',
            ],
        ],
        [
            'table' => 'log',
            'call' => 'dropForeign',
            'data' => [
                'name' => 'blame',
            ],
        ],
        [
            'table' => 'log',
            'call' => 'renameColumn',
            'data' => [
                'col' => 'blame',
                'name' => 'user',
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
            'call' => 'updateValue',
            'data' => [
                'name' => 'merge_posts',
                'value' => '1'
            ],
        ],
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.2'
            ],
        ]
    ],
    '0.3' => [
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'registration_off',
                'value' => '0'
            ],
        ],
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.3'
            ],
        ]
    ],
    '0.4' => [
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.4'
            ],
        ]
    ],
    '0.5' => [
        [
            'table' => 'user_activity',
            'call' => 'dropIndex',
            'data' => [
                'name' => 'url',
            ],
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
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'allow_polls',
                'value' => '1'
            ],
        ],
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.5'
            ],
        ]
    ],
    '0.6' => [
        [
            'table' => 'user',
            'call' => 'dropColumn',
            'data' => [
                'col' => 'timezone',
            ],
        ],
        [
            'table' => 'user',
            'call' => 'dropColumn',
            'data' => [
                'col' => 'anonymous',
            ],
        ],
        [
            'table' => 'user_meta',
            'call' => 'addColumn',
            'data' => [
                'col' => 'timezone',
                'type' => Schema::TYPE_STRING . '(45)',
            ],
        ],
        [
            'table' => 'user_meta',
            'call' => 'addColumn',
            'data' => [
                'col' => 'anonymous',
                'type' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 0',
            ],
        ],
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'use_wysiwyg',
                'value' => '1'
            ],
        ],
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.6'
            ],
        ]
    ],
    '0.7' => [
        [
            'table' => 'config',
            'call' => 'updateValue',
            'data' => [
                'name' => 'version',
                'value' => '0.7'
            ],
        ]
    ],
];
