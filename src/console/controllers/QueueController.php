<?php

namespace bizley\podium\console\controllers;

use bizley\podium\models\Email;
use yii\console\Controller;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;


class QueueController extends Controller
{

    const DEFAULT_BATCH_LIMIT = 100;
    
    /**
     * @var string the name of the table for email queue.
     */
    public $queueTable = '{{%podium_email}}';
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';
    
    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['queueTable', 'db']
        );
    }
    
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->db = Instance::ensure($this->db, Connection::className());
            return true;
        } else {
            return false;
        }
    }

    public function getNewBatch($limit = 0)
    {
        if (!is_numeric($limit) || $limit <= 0) {
            $limit = self::DEFAULT_BATCH_LIMIT;
        }
        
        return (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_PENDING])->orderBy(['id' => SORT_ASC])->limit((int)$limit)->all();
    }

    public function actionRun($limit = 0)
    {
        $emails = $this->getNewBatch($limit);
        if (empty($emails)) {
            $this->stdout("No pending emails in queue found.\n", Console::FG_GREEN);

            return self::EXIT_CODE_NORMAL;
        }

        $total = count($emails);
        $this->stdout("$total pending " . ($total === 1 ? 'email' : 'emails') . " to be sent:\n", Console::FG_YELLOW);

        $errors = false;
        foreach ($emails as $email) {
            if (!$this->send($email)) {
                $errors = true;
            }
        }
        $this->stdout("\n");

        if ($errors) {
            $this->stdout("\nBatch sent with errors.\n", Console::FG_RED);
        }
        else {
            $this->stdout("\nBatch sent successfully.\n", Console::FG_GREEN);
        }
    }
    
    public function actionCheck()
    {
        $this->stdout("Queue status:\n");
        
        $pending = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_PENDING])->count();
        $sent    = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_SENT])->count();
        $gaveup  = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_GAVEUP])->count();
        
        $this->stdout("$pending pending " . ($pending === 1 ? 'email' : 'emails') . ".\n", Console::FG_YELLOW);
        $this->stdout("$sent sent " . ($sent === 1 ? 'email' : 'emails') . ".\n", Console::FG_GREEN);
        $this->stdout("$gaveup " . ($gaveup === 1 ? 'email' : 'emails') . " stucked.\n", Console::FG_RED);
    }
}
