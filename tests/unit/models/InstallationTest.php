<?php

namespace podium\models;

use bizley\podium\maintenance\Installation;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use UnitTester;
use yii\db\Schema;

class InstallationTest extends Unit
{
    /**
     * @var UnitTester
     */
    public $tester;
    
    public $steps = [
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
    ];
    
    public function testNextStep()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps]);
        $result = $install->nextStep();
        expect($result['drop'])->equals(false);
        expect($result['type'])->equals(Installation::TYPE_SUCCESS);
    }

    public function testNextDrop()
    {
        $install = Stub::construct(Installation::className(), [], ['_steps' => $this->steps]);
        $result = $install->nextDrop();
        expect_that($result['drop']);
        expect($result['type'])->equals(Installation::TYPE_WARNING);
    }
}
