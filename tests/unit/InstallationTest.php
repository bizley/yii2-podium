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
        'createTest' => [
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
            ]
        ]
    ];
    
    public static function tearDownAfterClass()
    {
        $db = (new Installation)->db;
        $tables = ['log'];
        foreach ($tables as $table) {
            if ($db->schema->getTableSchema("{{%podium_$table}}", true) !== null) {
                $db->createCommand()->dropTable("{{%podium_$table}}")->execute();
            }
        }
        parent::tearDownAfterClass();
    }
    
    public function testCreateTable()
    {
        \Yii::$app->session->remove(Installation::SESSION_KEY);
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['createTest']]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
    }

    /**
     * @depends testCreateTable
     */
    public function testDropTable()
    {
        \Yii::$app->session->remove(Installation::SESSION_KEY);
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps['createTest']]);
        $result = $install->nextDrop();
        expect_that($result['drop']);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
    }
}
