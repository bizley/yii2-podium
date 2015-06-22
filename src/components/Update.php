<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use Exception;
use Yii;
use yii\db\Schema;

/**
 * Podium Update
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\DbManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Update extends Maintenance
{

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
            if (!isset(static::steps()[(int)$step])) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the requested installation step.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            elseif ($this->getInstallationSteps() == 0) {
                $this->setResult($this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Can not find the installation steps.')));
                $this->setError(true);
                $this->setPercent(100);
            }
            else {
                $this->setPercent($this->getInstallationSteps() == (int)$step + 1 ? 100 : floor(100 * ((int)$step + 1) / $this->getInstallationSteps()));
                $this->_proceedStep(static::steps()[(int)$step]);
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
    public function getInstallationSteps()
    {
        if ($this->_installationSteps === null) {
            $v = $this->getVersion();
            if ($v === null) {
                $all = [];
                foreach (static::steps() as $version => $data) {
                    $all = array_merge($all, $data);
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
                $all  = [];
                foreach ($part as $version => $data) {
                    $all = array_merge($all, $data);
                }
            }
            $this->_installationSteps = count($all);
        }
        return $this->_installationSteps;
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
     * Sets version.
     * @param string $value
     */
    public function setVersion($value)
    {
        $this->_version = $value;
    }
    
    /**
     * Installation steps.
     */
    public static function steps()
    {
        return [
            '0.1' => []
        ];
    }
}