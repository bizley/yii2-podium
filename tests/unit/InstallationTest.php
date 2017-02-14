<?php

namespace podium;

use bizley\podium\maintenance\Installation;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use yii\db\Schema;

class InstallationTest extends Unit
{
    /**
     * @var UnitTester
     */
    public $tester;

    public $steps = [
        'createTableTest' => [
            [
                'table' => 'test',
                'call' => 'createTable',
                'data' => [
                    'schema' => [
                        'id' => Schema::TYPE_PK,
                        'level' => Schema::TYPE_INTEGER,
                        'category' => Schema::TYPE_STRING,
                        'message' => Schema::TYPE_TEXT,
                        'key_f' => Schema::TYPE_INTEGER,
                    ],
                ],
            ]
        ],
        'addColumnTest' => [
            [
                'table' => 'test',
                'call' => 'addColumn',
                'data' => [
                    'col' => 'added',
                    'type' => Schema::TYPE_INTEGER,
                ],
            ]
        ],
        'dropColumnTest' => [
            [
                'table' => 'test',
                'call' => 'dropColumn',
                'data' => [
                    'col' => 'added',
                ],
            ]
        ],
        'addIndexTest' => [
            [
                'table' => 'test',
                'call' => 'addIndex',
                'data' => [
                    'name' => 'sort',
                    'cols' => ['level'],
                ],
            ]
        ],
        'dropIndexTest' => [
            [
                'table' => 'test',
                'call' => 'dropIndex',
                'data' => [
                    'name' => 'sort',
                ],
            ]
        ],
        'addForeignTest' => [
            [
                'table' => 'test',
                'call' => 'addForeign',
                'data' => [
                    'key' => 'key_f',
                    'ref' => 'test_foreign',
                    'col' => 'id',
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ],
            ]
        ],
        'dropForeignTest' => [
            [
                'table' => 'test',
                'call' => 'dropForeign',
                'data' => [
                    'name' => 'key_f',
                ],
            ]
        ],
        'alterColumnTest' => [
            [
                'table' => 'test',
                'call' => 'alterColumn',
                'data' => [
                    'col' => 'message',
                    'type' => Schema::TYPE_STRING,
                ],
            ]
        ],
        'renameColumnTest' => [
            [
                'table' => 'test',
                'call' => 'renameColumn',
                'data' => [
                    'col' => 'message',
                    'name' => 'messageRenamed',
                ],
            ]
        ],
    ];

    protected function _before()
    {
        parent::_before();
        \Yii::$app->session->remove(Installation::SESSION_KEY);
    }

    public static function tearDownAfterClass()
    {
        $db = (new Installation)->db;
        $tables = ['test', 'test_foreign'];
        foreach ($tables as $table) {
            if ($db->schema->getTableSchema("{{%podium_$table}}", true) !== null) {
                $db->createCommand()->dropTable("{{%podium_$table}}")->execute();
            }
        }
        parent::tearDownAfterClass();
    }

    public function testCreateTable()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['createTableTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table podium_test has been created');
        expect($result['table'])->equals('podium_test');
        expect_that($install->db->schema->getTableSchema("{{%podium_test}}", true));
    }

    /**
     * @depends testCreateTable
     */
    public function testAddColumn()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['addColumnTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table column added has been added');
        expect($result['table'])->equals('podium_test');
        expect($install->db->schema->getTableSchema("{{%podium_test}}", true)->columns)->hasKey('added');
    }

    /**
     * @depends testAddColumn
     */
    public function testDropColumn()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['dropColumnTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
        expect($result['result'])->equals('Table column added has been dropped');
        expect($result['table'])->equals('podium_test');
        expect($install->db->schema->getTableSchema("{{%podium_test}}", true)->columns)->hasntKey('added');
    }

    /**
     * @depends testCreateTable
     */
    public function testAddIndex()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['addIndexTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table index idx-podium_test-sort has been added');
        expect($result['table'])->equals('podium_test');
    }

    /**
     * @depends testAddIndex
     */
    public function testDropIndex()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['dropIndexTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
        expect($result['result'])->equals('Table index idx-podium_test-sort has been dropped');
        expect($result['table'])->equals('podium_test');
    }

    /**
     * @depends testCreateTable
     */
    public function testAddForeign()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['addForeignTest']]);
        $install->db->createCommand()->createTable('{{%podium_test_foreign}}', [
            'id' => Schema::TYPE_PK,
            'level' => Schema::TYPE_INTEGER
        ], $install->tableOptions)->execute();
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table foreign key fk-podium_test-key_f has been added');
        expect($result['table'])->equals('podium_test');
    }

    /**
     * @depends testAddForeign
     */
    public function testDropForeign()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['dropForeignTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
        expect($result['result'])->equals('Table foreign key fk-podium_test-key_f has been dropped');
        expect($result['table'])->equals('podium_test');
        $install->db->createCommand()->dropTable("{{%podium_test_foreign}}")->execute();
    }

    /**
     * @depends testCreateTable
     */
    public function testAlterColumn()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['alterColumnTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table column message has been updated');
        expect($result['table'])->equals('podium_test');
    }

    /**
     * @depends testCreateTable
     */
    public function testRenameColumn()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['renameColumnTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
        expect($result['result'])->equals('Table column message has been renamed to messageRenamed');
        expect($result['table'])->equals('podium_test');
        expect($install->db->schema->getTableSchema("{{%podium_test}}", true)->columns)->hasntKey('message');
        expect($install->db->schema->getTableSchema("{{%podium_test}}", true)->columns)->hasKey('messageRenamed');
    }


    /**
     * @depends testCreateTable
     */
    public function testDropTable()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['createTableTest']]);
        $result = $install->nextDrop();
        expect_that($result['drop']);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
        expect($result['result'])->equals('Table podium_test has been dropped');
        expect($result['table'])->equals('podium_test');
        expect_not($install->db->schema->getTableSchema("{{%podium_test}}", true));
    }
}

