<?php

namespace bizley\podium\maintenance;

use bizley\podium\models\Post;
use bizley\podium\Module as Podium;
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
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property boolean $type
 * @property string $foreignName
 * @property string $indexName
 * @property Podium $module
 * @property integer $numberOfSteps
 * @property integer $percent
 * @property string $result
 * @property string $table
 * @property string $tableOptions
 */
class Maintenance extends Component
{
    const TYPE_SUCCESS = 0;
    const TYPE_WARNING = 1;
    const TYPE_ERROR = 2;
    
    /**
     * @var DbManager authorization manager.
     */
    public $authManager = 'authManager';
    
    /**
     * @var Connection database connection.
     */
    public $db = 'db';

    /**
     * @var bool error flag.
     */
    protected $_type = self::TYPE_SUCCESS;
    
    /**
     * @var string Podium database prefix.
     */
    protected $_prefix = 'podium_';
    
    /**
     * @var string current table name.
     */
    protected $_table;
    
    /**
     * @var string additional SQL fragment that will be appended to the generated SQL.
     */
    protected $_tableOptions = null;
    
    /**
     * Adds column to database table.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function addColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        
        try {
            $this->db->createCommand()->addColumn($this->table, $data['col'], $data['type'])->execute();
            return Yii::t('podium/flash', 'Table column {name} has been added', [
                'name' => $data['col']
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table column {name} adding', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Creates database table foreign key.
     * @param array $data installation step data.
     * @return string result message.
     * @since 0.2
     */
    protected function addForeign($data)
    {
        if (empty($data['key']) || (!is_string($data['key']) && !is_array($data['key']))) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        if (empty($data['ref']) || !is_string($data['ref'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Foreign key reference missing.');
        }
        if (empty($data['col']) || (!is_string($data['col']) && !is_array($data['col']))) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Referenced columns missing.');
        }
        
        try {
            $this->db->createCommand()->addForeignKey(
                    $this->getForeignName($data['key']), 
                    $this->table, 
                    $data['key'], 
                    $this->getTableName($data['ref']), 
                    $data['col'],
                    !empty($data['delete']) ? $data['delete'] : null,
                    !empty($data['update']) ? $data['update'] : null
                )
                ->execute();
            return Yii::t('podium/flash', 'Table foreign key {name} has been added', [
                'name' => $this->getForeignName($data['key'])
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table foreign key {name} adding', [
                    'name' => $this->getForeignName($data['key'])
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Creates database table index.
     * @param array $data installation step data.
     * @return string result message.
     * @since 0.2
     */
    protected function addIndex($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        if (empty($data['cols']) || !is_array($data['cols'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Index columns missing.');
        }
        
        try {
            $this->db->createCommand()->createIndex($this->getIndexName($data['name']), $this->table, $data['cols'])->execute();
            return Yii::t('podium/flash', 'Table index {name} has been added', [
                'name' => $this->getIndexName($data['name'])
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table index {name} adding', [
                    'name' => $this->getIndexName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Modifies database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function alterColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        
        try {
            $this->db->createCommand()->alterColumn($this->table, $data['col'], $data['type'])->execute();
            return Yii::t('podium/flash', 'Table column {name} has been updated', [
                'name' => $data['col']
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table column {name} updating', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Returns percent.
     * Clears cache at 100.
     * @param int $currentStep
     * @param int $maxStep
     * @return int
     * @since 0.2
     */
    public function countPercent($currentStep, $maxStep)
    {
        $percent = $maxStep ? round(100 * $currentStep / $maxStep) : 0;
        if ($percent > 100) {
            $percent = 100;
        }
        if ($percent == 100 && $currentStep != $maxStep) {
            $percent = 99;
        }
        if ($percent == 100) {
            $this->clearCache();
        }
        return $percent;
    }
    
    /**
     * Creates database table.
     * @param array $data installation step data.
     * @return string result message.
     * @since 0.2
     */
    protected function createTable($data)
    {
        if (empty($data['schema']) || !is_array($data['schema'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Database schema missing.');
        }
        try {
            $this->db->createCommand()->createTable($this->table, $data['schema'], $this->getTableOptions())->execute();
            return Yii::t('podium/flash', 'Table {name} has been created', [
                'name' => $this->getTable(true)
            ]);
        } catch (Exception $e) {
            if ($this->_table != 'log') {
                // in case of creating log table don't try to log error in it if it's not available
                Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            }
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table {name} creating', [
                    'name' => $this->getTable(true)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Drops current database table if it exists.
     * @return string|boolean result message or true if no table to drop.
     */
    protected function dropTable()
    {
        try {
            if ($this->db->schema->getTableSchema($this->table, true) !== null) {
                $this->db->createCommand()->dropTable($this->table)->execute();
                $this->type = self::TYPE_WARNING;
                return Yii::t('podium/flash', 'Table {name} has been dropped', [
                    'name' => $this->getTable(true)
                ]);
            }
            return true;
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table {name} dropping', [
                    'name' => $this->getTable(true)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Drops database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function dropColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        
        try {
            $this->db->createCommand()->dropColumn($this->table, $data['col'])->execute();
            return Yii::t('podium/flash', 'Table column {name} has been dropped', [
                'name' => $data['col']
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table column {name} dropping', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Drops database table foreign key.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function dropForeign($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        
        try {
            $this->db->createCommand()->dropForeignKey($this->getForeignName($data['name']), $this->table)->execute();
            return Yii::t('podium/flash', 'Table foreign key {name} has been dropped', [
                'name' => $this->getForeignName($data['name'])
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table foreign key {name} dropping', [
                    'name' => $this->getForeignName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Drops database table index.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function dropIndex($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        
        try {
            $this->db->createCommand()->dropIndex($this->getIndexName($data['name']), $this->table)->execute();
            return Yii::t('podium/flash', 'Table index {name} has been dropped', [
                'name' => $this->getIndexName($data['name'])
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table index {name} dropping', [
                    'name' => $this->getIndexName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    
    
    /**
     * Renames database table.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function rename($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! New table name missing.');
        }
        
        try {
            $this->db->createCommand()->renameTable($this->table, $this->getTableName($data['name']))->execute();
            return Yii::t('podium/flash', 'Table {name} has been renamed to {new}', [
                'name' => $this->getTable(true), 
                'new'  => $this->getTableName($data['name'])
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table {name} renaming to {new}', [
                    'name' => $this->getTable(true), 
                    'new'  => $this->getTableName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Renames database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function renameColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($data['name']) || !is_string($data['name'])) {
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Installation aborted! New column name missing.');
        }
        
        try {
            $this->db->createCommand()->renameColumn($this->table, $data['col'], $data['name'])->execute();
            return Yii::t('podium/flash', 'Table column {name} has been renamed to {new}', [
                'name' => $data['col'], 
                'new'  => $data['name']
            ]);
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->type = self::TYPE_ERROR;
            return Yii::t('podium/flash', 'Error during table column {name} renaming to {new}', [
                    'name' => $data['col'], 
                    'new'  => $data['name']
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }
    
    /**
     * Checks if Post database table exists.
     * This is taken as verification of Podium installation.
     * @return bool whether Post database table exists.
     */
    public static function check()
    {
        try {
            (new Post)->tableSchema;
        } catch (Exception $e) {
            // Prepare for installation.
            // No log because table might not be available.
            return false;
        }
        return true;
    }
    
    /**
     * Returns result type flag.
     * @return int
     * @since 0.2
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * Returns prefixed foreign key name.
     * @param string $name
     * @return string
     */
    public function getForeignName($name)
    {
        return 'fk-' . $this->_table . '-' . (is_array($name) 
            ? implode('_', $name) 
            : $name);
    }
    
    /**
     * Returns prefixed index name.
     * @param string $name
     * @return string
     */
    public function getIndexName($name)
    {
        return 'idx-' . $this->_table . '-' . $name;
    }
    
    /**
     * Returns Podium instance.
     * @return Podium
     */
    public function getModule()
    {
        return Podium::getInstance();
    }
    
    /**
     * Returns table name.
     * @param bool $raw whether return raw name
     * @return string
     */
    public function getTable($raw = false)
    {
        if ($raw) {
            return $this->_table;
        }
        return $this->_table == '...' ? '...' : $this->getTableName($this->_table);
    }
    
    /**
     * Returns prefixed table name.
     * @param string $name
     * @return string
     */
    public function getTableName($name)
    {
        return '{{%' . $this->_prefix . $name . '}}';
    }
    
    /**
     * Returns table options string.
     * @return string
     */
    public function getTableOptions()
    {
        return $this->_tableOptions;
    }
    
    /**
     * Initialise component.
     */
    public function init()
    {
        parent::init();

        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->db->driverName === 'mysql') {
            $this->setTableOptions('CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        }
        $this->authManager = Instance::ensure($this->authManager, DbManager::className());
    }
    
    /**
     * Clears cache.
     * @since 0.2
     */
    public function clearCache()
    {
        $this->module->cache->flush();
    }
    
    /**
     * Sets result type flag.
     * @param int $value
     * @since 0.2
     */
    public function setType($value)
    {
        $this->_type = $value;
    }
    
    /**
     * Sets table name.
     * @param string $value
     */
    public function setTable($value)
    {
        $this->_table = $value;
    }
    
    /**
     * Sets table options string.
     * @param string $value
     */
    public function setTableOptions($value)
    {
        $this->_tableOptions = $value;
    }
    
    /**
     * Installation steps to be set.
     * This should be overriden.
     */
    public function getSteps()
    {
        throw new Exception('This method must be overriden in Installation and Update class!');
    }
}
