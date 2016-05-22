<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\console;

use bizley\podium\components\Config;
use bizley\podium\log\Log;
use bizley\podium\models\Email;
use Exception;
use Yii;
use yii\console\Controller;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;
use yii\mail\BaseMailer;

/**
 * Podium command line tool to send emails.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class QueueController extends Controller
{

    const DEFAULT_BATCH_LIMIT = 100;
    
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $db = 'db';
    
    /**
     * @var string controller default action ID.
     */
    public $defaultAction = 'run';

    /**
     * @var integer the limit of emails sent in one batch (default 100).
     */
    public $limit = self::DEFAULT_BATCH_LIMIT;
    
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection to use
     * when applying migrations. Starting from version 2.0.3, this can also be a configuration array
     * for creating the object.
     */
    public $mailer = 'mailer';
    
    /**
     * @var string the name of the table for email queue.
     */
    public $queueTable = '{{%podium_email}}';
    
    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['queueTable', 'db', 'mailer']
        );
    }
    
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the db and mailer components.
     * @param \yii\base\Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        try {
            if (parent::beforeAction($action)) {
                $this->db = Instance::ensure($this->db, Connection::className());
                $this->mailer = Instance::ensure($this->mailer, BaseMailer::className());
                return true;
            }
        }
        catch (Exception $e) {
            $this->stderr("ERROR: " . $e->getMessage() . "\n");
        }
        return false;
    }

    /**
     * Returns new batch of emails.
     * @param integer $limit maximum number of rows in batch
     * @return array
     */
    public function getNewBatch($limit = 0)
    {
        try {
            if (!is_numeric($limit) || $limit <= 0) {
                $limit = $this->limit;
            }
            return (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_PENDING])->orderBy(['id' => SORT_ASC])->limit((int)$limit)->all();
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }

    /**
     * Sends email using mailer component.
     * @param string $email
     * @param string $fromName
     * @param string $fromEmail
     * @return boolean
     */
    public function send($email, $fromName, $fromEmail)
    {
        try {
            $mailer = Yii::$app->mailer->compose();
            $mailer->setFrom([$fromEmail => $fromName]);
            $mailer->setTo($email['email']);
            $mailer->setSubject($email['subject']);
            $mailer->setHtmlBody($email['content']);
            $mailer->setTextBody(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $email['content'])));

            return $mailer->send();
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }

    /**
     * Tries to send email from queue and updates its status.
     * @param string $email
     * @param string $fromName
     * @param string $fromEmail
     * @param integer $maxAttempts
     * @return boolean
     */
    public function process($email, $fromName, $fromEmail, $maxAttempts)
    {
        try {
            if ($this->send($email, $fromName, $fromEmail)) {
                $this->db->createCommand()->update($this->queueTable, ['status' => Email::STATUS_SENT], ['id' => $email['id']])->execute();
                return true;
            }

            $attempt = $email['attempt'] + 1;
            if ($attempt <= $maxAttempts) {
                $this->db->createCommand()->update($this->queueTable, ['attempt' => $attempt], ['id' => $email['id']])->execute();
            }
            else {
                $this->db->createCommand()->update($this->queueTable, ['status' => Email::STATUS_GAVEUP], ['id' => $email['id']])->execute();
            }
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Runs the queue.
     * @param integer $limit
     * @return integer|void
     */
    public function actionRun($limit = 0)
    {
        $version = $this->module->version;
        $this->stdout("\nPodium mail queue v{$version}\n");
        $this->stdout("------------------------------\n");
        
        $emails = $this->getNewBatch($limit);
        if (empty($emails)) {
            $this->stdout("No pending emails in the queue found.\n\n", Console::FG_GREEN);
            return self::EXIT_CODE_NORMAL;
        }

        $total = count($emails);
        $this->stdout("\n$total pending " . ($total === 1 ? 'email' : 'emails') . " to be sent now:\n", Console::FG_YELLOW);

        $errors = false;
        foreach ($emails as $email) {
            if (!$this->process($email, Config::getInstance()->get('from_name'), Config::getInstance()->get('from_email'), Config::getInstance()->get('max_attempts'))) {
                $errors = true;
            }
        }

        if ($errors) {
            $this->stdout("\nBatch sent with errors.\n\n", Console::FG_RED);
        }
        else {
            $this->stdout("\nBatch sent successfully.\n\n", Console::FG_GREEN);
        }
    }
    
    /**
     * Checks the current status for the mail queue.
     */
    public function actionCheck()
    {
        $version = $this->module->version;
        $this->stdout("\nPodium mail queue check v{$version}\n");
        $this->stdout("------------------------------\n");
        $this->stdout(" EMAILS  | COUNT\n");
        $this->stdout("------------------------------\n");
        
        $pending = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_PENDING])->count();
        $sent    = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_SENT])->count();
        $gaveup  = (new Query)->from($this->queueTable)->where(['status' => Email::STATUS_GAVEUP])->count();
        
        $showPending = $this->ansiFormat($pending, Console::FG_YELLOW);
        $showSent    = $this->ansiFormat($sent, Console::FG_GREEN);
        $showGaveup  = $this->ansiFormat($gaveup, Console::FG_RED);
        
        $this->stdout(" pending | $showPending\n");
        $this->stdout(" sent    | $showSent\n");
        $this->stdout(" stucked | $showGaveup\n");
        $this->stdout("------------------------------\n\n");
    }
}
