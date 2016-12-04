<?php

namespace bizley\podium\maintenance;

use bizley\podium\models\Post;
use bizley\podium\Podium;
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
 * @property string $foreignName
 * @property string $indexName
 * @property Podium $module
 * @property integer $numberOfSteps
 * @property integer $percent
 * @property string $result
 * @property string $table
 * @property string $rawTable
 */
class Maintenance extends Component
{
    const TYPE_SUCCESS = 0;
    const TYPE_WARNING = 1;
    const TYPE_ERROR = 2;

    /**
     * @var DbManager authorization manager.
     */
    public $authManager;

    /**
     * @var Connection database connection.
     */
    public $db;

    /**
     * @var int returned status.
     * @since 0.6
     */
    public $type = self::TYPE_ERROR;

    /**
     * @var string additional SQL fragment that will be appended to the generated SQL.
     * @since 0.6
     */
    public $tableOptions;

    /**
     * @var string current table name.
     */
    protected $_table;


    /**
     * Adds column to database table.
     * @param string $col column name
     * @param string $type column schema
     * @return string result message
     */
    protected function addColumn($col, $type)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($type)) {
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        try {
            $this->db->createCommand()->addColumn($this->table, $col, $type)->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table column {name} has been added', ['name' => $col]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table column {name} adding', ['name' => $col]) 
                    . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Creates database table foreign key.
     * @param string|array $key key columns
     * @param string $ref key reference table
     * @param string|array $col reference table columns
     * @param string $delete ON DELETE action
     * @param string $update ON UPDATE action
     * @return string result message
     * @since 0.2
     */
    protected function addForeign($key, $ref, $col, $delete = null, $update = null)
    {
        if (empty($key)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        if (empty($ref)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key reference missing.');
        }
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Referenced columns missing.');
        }
        try {
            $this->db->createCommand()->addForeignKey(
                    $this->getForeignName($key), $this->table, $key, 
                    $this->getTableName($ref), $col, $delete, $update
                )->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table foreign key {name} has been added', [
                'name' => $this->getForeignName($key)
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table foreign key {name} adding', [
                    'name' => $this->getForeignName($key)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Creates database table index.
     * @param string $name index name
     * @param array $cols columns
     * @return string result message
     * @since 0.2
     */
    protected function addIndex($name, $cols)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        if (empty($cols)) {
            return Yii::t('podium/flash', 'Installation aborted! Index columns missing.');
        }
        try {
            $this->db->createCommand()->createIndex($this->getIndexName($name), $this->table, $cols)->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table index {name} has been added', [
                'name' => $this->getIndexName($name)
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table index {name} adding', [
                    'name' => $this->getIndexName($name)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Modifies database table column.
     * @param string $col column name
     * @param string $type column schema
     * @return string result message.
     */
    protected function alterColumn($col, $type)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($type)) {
            return Yii::t('podium/flash', 'Installation aborted! Column type missing.');
        }
        try {
            $this->db->createCommand()->alterColumn($this->table, $col, $type)->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table column {name} has been updated', ['name' => $col]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table column {name} updating', ['name' => $col]) 
                    . ': ' . Html::tag('pre', $e->getMessage());
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
     * @param array $schema table schema
     * @return string result message
     * @since 0.2
     */
    protected function createTable($schema)
    {
        if (empty($schema)) {
            return Yii::t('podium/flash', 'Installation aborted! Database schema missing.');
        }
        try {
            $this->db->createCommand()->createTable($this->table, $schema, $this->tableOptions)->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table {name} has been created', [
                'name' => $this->rawTable
            ]);
        } catch (Exception $e) {
            if ($this->_table != 'log') {
                // in case of creating log table don't try to log error in it if it's not available
                Yii::error($e->getMessage(), __METHOD__);
            }
            return Yii::t('podium/flash', 'Error during table {name} creating', [
                    'name' => $this->rawTable
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Drops current database table if it exists.
     * @return string|bool result message or true if no table to drop.
     */
    protected function dropTable()
    {
        try {
            if ($this->db->schema->getTableSchema($this->table, true) !== null) {
                $this->db->createCommand()->dropTable($this->table)->execute();
                $this->type = self::TYPE_WARNING;
                return Yii::t('podium/flash', 'Table {name} has been dropped', [
                    'name' => $this->rawTable
                ]);
            }
            return true;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table {name} dropping', [
                    'name' => $this->rawTable
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Drops database table column.
     * @param string $col column name
     * @return string result message
     */
    protected function dropColumn($col)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        try {
            $this->db->createCommand()->dropColumn($this->table, $col)->execute();
            $this->type = self::TYPE_WARNING;
            return Yii::t('podium/flash', 'Table column {name} has been dropped', ['name' => $col]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table column {name} dropping', ['name' => $col]) 
                    . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Drops database table foreign key.
     * @param string $name key name
     * @return string result message
     */
    protected function dropForeign($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.');
        }
        try {
            $this->db->createCommand()->dropForeignKey($this->getForeignName($name), $this->table)->execute();
            $this->type = self::TYPE_WARNING;
            return Yii::t('podium/flash', 'Table foreign key {name} has been dropped', [
                'name' => $this->getForeignName($name)
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table foreign key {name} dropping', [
                    'name' => $this->getForeignName($name)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Drops database table index.
     * @param string $name index name
     * @return string result message
     */
    protected function dropIndex($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! Index name missing.');
        }
        try {
            $this->db->createCommand()->dropIndex($this->getIndexName($name), $this->table)->execute();
            $this->type = self::TYPE_WARNING;
            return Yii::t('podium/flash', 'Table index {name} has been dropped', [
                'name' => $this->getIndexName($name)
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table index {name} dropping', [
                    'name' => $this->getIndexName($name)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Renames database table.
     * @param string $name table new name
     * @return string result message
     */
    protected function rename($name)
    {
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! New table name missing.');
        }
        try {
            $this->db->createCommand()->renameTable($this->table, $this->getTableName($name))->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table {name} has been renamed to {new}', [
                'name' => $this->rawTable, 
                'new'  => $this->getTableName($name)
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table {name} renaming to {new}', [
                    'name' => $this->rawTable, 
                    'new'  => $this->getTableName($name)
                ]) . ': ' . Html::tag('pre', $e->getMessage());
        }
    }

    /**
     * Renames database table column.
     * @param string $col column name
     * @param string $name column new name
     * @return string result message
     */
    protected function renameColumn($col, $name)
    {
        if (empty($col)) {
            return Yii::t('podium/flash', 'Installation aborted! Column name missing.');
        }
        if (empty($name)) {
            return Yii::t('podium/flash', 'Installation aborted! New column name missing.');
        }
        try {
            $this->db->createCommand()->renameColumn($this->table, $col, $name)->execute();
            $this->type = self::TYPE_SUCCESS;
            return Yii::t('podium/flash', 'Table column {name} has been renamed to {new}', [
                'name' => $col, 
                'new'  => $name
            ]);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return Yii::t('podium/flash', 'Error during table column {name} renaming to {new}', [
                    'name' => $col, 
                    'new'  => $name
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
            (new Post())->tableSchema;
        } catch (Exception $e) {
            // Prepare for installation.
            // No log because table might not be available.
            return false;
        }
        return true;
    }

    /**
     * Returns prefixed foreign key name.
     * @param string $name
     * @return string
     */
    public function getForeignName($name)
    {
        return 'fk-' . $this->_table . '-' . (is_array($name) ? implode('_', $name) : $name);
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
     * Returns raw table name.
     * @return string
     * @since 0.6
     */
    public function getRawTable()
    {
        return $this->_table;
    }
    
    /**
     * Returns table name.
     * @return string
     */
    public function getTable()
    {
        return $this->_table == '...' ? '...' : $this->getTableName($this->_table);
    }
    
    /**
     * Returns prefixed table name.
     * @param string $name
     * @return string
     */
    public function getTableName($name)
    {
        return '{{%podium_' . $name . '}}';
    }
    
    /**
     * Initialize component.
     */
    public function init()
    {
        parent::init();

        $this->db = Instance::ensure(Podium::getInstance()->db, Connection::className());
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->authManager = Instance::ensure(Podium::getInstance()->rbac, DbManager::className());
    }
    
    /**
     * Clears cache.
     * @since 0.2
     */
    public function clearCache()
    {
        $this->module->podiumCache->flush();
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
     * Installation steps to be set.
     * This should be overriden.
     */
    public function getSteps()
    {
        throw new Exception('This method must be overriden in Installation and Update class!');
    }
}
