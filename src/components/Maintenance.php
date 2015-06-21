<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\models\User;
use Exception;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Html;
use yii\rbac\DbManager;

/**
 * Podium Maintenance module
 * Maintenance requires database connection to be configured first.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\DbManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Maintenance extends Component
{

    /**
     * @var DbManager authorization manager.
     */
    public $authManager = 'authManager';
    
    /**
     * @var Connection database connection.
     */
    public $db = 'db';

    /**
     * @var boolean error flag.
     */
    protected $_error = false;
    
    protected $_table;
    protected $_result = '';

    /**
     * @var string Podium database prefix.
     */
    protected $_prefix = 'podium_';

    /**
     * @var string additional SQL fragment that will be appended to the generated SQL.
     */
    protected $_tableOptions = null;

    public function getIndexName($name)
    {
        return 'idx-' . $this->_table . '-' . $name;
    }
    
    public function getForeignName($name)
    {
        return 'fk-' . $this->_table . '-' . (is_array($name) ? implode('_', $name) : $name);
    }
    
    /**
     * Creates database table index.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _index($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index name missing.'));
        }
        if (empty($data['cols']) || !is_array($data['cols'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index columns missing.'));
        }
        try {
            $this->db->createCommand()->createIndex($this->getIndexName($data['name']), $this->getTable(), $data['cols'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index has been added'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table index adding') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Creates database table foreign key.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _foreign($data)
    {
        if (empty($data['key']) || (!is_string($data['key']) && !is_array($data['key']))) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Key name missing.'));
        }
        if (empty($data['ref']) || !is_string($data['ref'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Key reference missing.'));
        }
        if (empty($data['col']) || (!is_string($data['col']) && !is_array($data['col']))) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Referenced columns missing.'));
        }
        try {
            $this->db->createCommand()->addForeignKey(
                    $this->getForeignName($data['key']), 
                    $this->getTable(), 
                    $data['key'], 
                    $this->getTableName($data['ref']), 
                    $data['col'],
                    !empty($data['delete']) ? $data['delete'] : null,
                    !empty($data['update']) ? $data['update'] : null
                )->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table foreign key has been added'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table foreign key adding') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Creates database table.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _create($data)
    {
        if (empty($data['schema']) || !is_array($data['schema'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Database schema missing.'));
        }
        try {
            $this->db->createCommand()->createTable($this->getTable(), $data['schema'], $this->getTableOptions())->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table has been created'));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table creating') . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }

    /**
     * Prepares error message.
     * @param string $content message content.
     * @return string prepared message.
     */
    public function outputDanger($content)
    {
        return Html::tag('span', $content, ['class' => 'text-danger']);
    }

    /**
     * Prepares success message.
     * @param string $content message content.
     * @return string prepared message.
     */
    public function outputSuccess($content)
    {
        return Html::tag('span', $content, ['class' => 'text-success']);
    }

    /**
     * Checks if User database table exists.
     * @return boolean wheter User database exists.
     */
    public static function check()
    {
        try {
            (new User())->getTableSchema();
            return true;
        }
        catch (Exception $e) {
            Yii::warning('Podium user database table not found - preparing for installation', __METHOD__);
        }

        return false;
    }

    public function getError()
    {
        return $this->_error;
    }
    
    public function setError($value)
    {
        $this->_error = $value ? true : false;
    }
    
    public function getTableOptions()
    {
        return $this->_tableOptions;
    }
    
    public function setTableOptions($value)
    {
        $this->_tableOptions = $value;
    }
    
    public function getResult()
    {
        return $this->_result;
    }
    
    public function setResult($value)
    {
        $this->_result = $value;
    }
    
    public function getTable()
    {
        return $this->_table == '...' ? '...' : $this->getTableName($this->_table);
    }
    
    public function getTableName($name)
    {
        return '{{%' . $this->_prefix . $name . '}}';
    }
    
    public function setTable($value)
    {
        $this->_table = $value;
    }
    
    /**
     * Initialise component.
     */
    public function init()
    {
        parent::init();

        try {
            $this->db = Instance::ensure($this->db, Connection::className());
            if ($this->db->driverName === 'mysql') {
                $this->setTableOptions('CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
            }
            $this->authManager = Instance::ensure($this->authManager, DbManager::className());
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
        }
    }
    
    protected function _drop($table)
    {
        try {
            if ($this->db->schema->getTableSchema($this->getTableName($table), true) !== null) {
                $this->db->createCommand()->dropTable($this->getTableName($table))->execute();
                return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been dropped.', ['name' => $this->getTableName($table)]));
            }
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table {name} dropping', ['name' => $this->getTableName($table)]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
}