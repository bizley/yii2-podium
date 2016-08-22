<?php

/**
 * Podium installation steps.
 * v0.2
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
            'call'  => 'renameColumn',
            'col'   => 'blame',
            'name'  => 'user',
        ],
        [
            'table'   => 'config',
            'call'    => 'updateVersion',
            'version' => '0.2'
        ]
    ]
];
