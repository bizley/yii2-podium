<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\maintenance;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use Exception;
use Yii;
use yii\db\Schema;
use yii\helpers\Html;

/**
 * Podium Update
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\BaseManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Update extends Maintenance
{

    /**
     * @var array list of steps for update.
     */
    protected $_partSteps;
    
    /**
     * @var string starting version.
     */
    protected $_version;
    
    /**
     * Proceeds next installation step.
     * @param array $data step data.
     * @throws Exception
     */
    protected function _proceedStep($data)
    {
        if (empty($data['table'])) {
            throw new Exception(Yii::t('podium/flash', 'Installation aborted! Database table name missing.'));
        }
        else {
            $this->setTable($data['table']);
            if (empty($data['call'])) {
                throw new Exception(Yii::t('podium/flash', 'Installation aborted! Action call missing.'));
            }
            else {
                $this->setError(false);
                switch ($data['call']) {
                    case 'create':
                        $result = call_user_func([$this, '_create'], $data);
                        break;
                    case 'addColumn':
                        $result = call_user_func([$this, '_addColumn'], $data);
                        break;
                    case 'alterColumn':
                        $result = call_user_func([$this, '_alterColumn'], $data);
                        break;
                    case 'drop':
                        $result = call_user_func([$this, '_drop'], $data);
                        break;
                    case 'dropColumn':
                        $result = call_user_func([$this, '_dropColumn'], $data);
                        break;
                    case 'dropIndex':
                        $result = call_user_func([$this, '_dropIndex'], $data);
                        break;
                    case 'dropForeign':
                        $result = call_user_func([$this, '_dropForeign'], $data);
                        break;
                    case 'index':
                        $result = call_user_func([$this, '_index'], $data);
                        break;
                    case 'foreign':
                        $result = call_user_func([$this, '_foreign'], $data);
                        break;
                    case 'rename':
                        $result = call_user_func([$this, '_rename'], $data);
                        break;
                    case 'renameColumn':
                        $result = call_user_func([$this, '_renameColumn'], $data);
                        break;
                    default:
                        $result = call_user_func([$this, '_' . $data['call']], $data);
                }
                
                $this->setResult($result);
                if ($this->getError()) {
                    $this->setPercent(100);
                }
            }
        }
    }
    
    /**
     * Starts next step of installation.
     * @param integer $step step number.
     * @param string|null $version starting version number.
     * @return array installation step result.
     */
    public function step($step, $version = null)
    {
        $this->setTable('...');
        $this->setVersion($version);
        try {
            if (!isset($this->getPartSteps()[(int)$step])) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            elseif ($this->getNumberOfSteps() == 0) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the installation steps.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            else {
                //Cache::getInstance()->flush();
                $this->setPercent($this->getNumberOfSteps() == (int)$step + 1 ? 100 : floor(100 * ((int)$step + 1) / $this->getNumberOfSteps()));
                $this->_proceedStep($this->getPartSteps()[(int)$step]);
            }
        }
        catch (Exception $e) {
            $this->setResult($this->outputDanger($e->getMessage()));
            $this->setError(true);
            $this->setPercent(100);
        }
        
        return [
            'table'   => $this->getTable(),
            'percent' => $this->getPercent(),
            'result'  => $this->getResult(),
            'error'   => $this->getError(),
        ];
    }
    
    /**
     * Counts number of installation steps.
     * @return int
     */
    public function getNumberOfSteps()
    {
        if ($this->_numberOfSteps === null) {
            $this->_numberOfSteps = count($this->getPartSteps());
        }
        return $this->_numberOfSteps;
    }
    
    /**
     * Counts number of installation steps.
     * @return int
     */
    public function getPartSteps()
    {
        if ($this->_partSteps === null) {
            $v = $this->getVersion();
            if ($v === null) {
                $this->_partSteps = [];
                foreach (static::steps() as $version => $data) {
                    $this->_partSteps = array_merge($this->_partSteps, $data);
                }
            }
            else {
                $found = false;
                $index = 1;
                foreach (static::steps() as $version => $data) {
                    if ($version == $v) {
                        $found = true;
                    }
                    $index++;
                    if ($found) {
                        break;
                    }
                }
                $part = array_slice(static::steps(), $index);
                $this->_partSteps  = [];
                foreach ($part as $version => $data) {
                    $this->_partSteps = array_merge($this->_partSteps, $data);
                }
            }
        }
        return $this->_partSteps;
    }
    
    /**
     * Gets version.
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }
    
    /**
     * @throws Exception
     */
    public function setPartSteps()
    {
        throw new Exception('Don\'t set update steps array directly!');
    }
    
    /**
     * Sets version.
     * @param string $value
     */
    public function setVersion($value)
    {
        $this->_version = $value;
    }
    
    /**
     * Updates database version number.
     * @param array $data
     * @return string result message.
     * @since 0.2
     */
    protected function _updateVersion($data)
    {
        try {
            if (empty($data['version'])) {
                throw new Exception(Yii::t('podium/flash', 'Version number missing.'));
            }
            Config::getInstance()->set('version', $data['version']);
            Cache::getInstance()->flush();
            return $this->outputSuccess(Yii::t('podium/flash', 'Database version has been updated to {version}.', ['version' => $data['version']]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during version updating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Installation steps.
     */
    public static function steps()
    {
        return [
            '0.2' => [
                [
                    'table'  => 'user_friend',
                    'call'   => 'create',
                    'schema' => [
                        'id'        => Schema::TYPE_PK,
                        'user_id'   => Schema::TYPE_INTEGER . ' NOT NULL',
                        'friend_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                    ],
                ],
                [
                    'table'  => 'user_friend',
                    'call'   => 'foreign',
                    'key'    => 'user_id',
                    'ref'    => 'user',
                    'col'    => 'id',
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ],
                [
                    'table'  => 'user_friend',
                    'call'   => 'foreign',
                    'key'    => 'friend_id',
                    'ref'    => 'user',
                    'col'    => 'id',
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ],
                [
                    'table'   => 'config',
                    'call'    => 'updateVersion',
                    'version' => '0.2'
                ]
            ]
        ];
    }
}
