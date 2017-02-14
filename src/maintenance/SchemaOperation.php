<?php

namespace bizley\podium\maintenance;

use bizley\podium\Podium;
use Exception;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Html;

/**
 * Podium Maintenance module
 * Maintenance requires database connection to be configured first.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property string $foreignName
 * @property string $indexName
 * @property Podium $module
 * @property string $table
 * @property string $rawTable
 */
class SchemaOperation extends Component
{
    const TYPE_SUCCESS = 0;
    const TYPE_WARNING = 1;
    const TYPE_ERROR = 2;

    /**
     * @var Connection database connection.
     */
    public $db;

    /**
     * @var int returned status.
     */
    public $type = self::TYPE_ERROR;

    /**
     * @var string additional SQL fragment that will be appended to the generated SQL.
     */
    public $tableOptions;

    /**
     * @var string current table name.
     */
    protected $_table;

    /**
     * @var string table name prefix.
     */
    protected $_prefix = 'podium_';

    /**
     * Initialize component.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->module->db, Connection::className());
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
    }

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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been added', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} adding', ['name' => $col])
            );
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been added', [
                'name' => $this->getForeignName($key)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table foreign key {name} adding', [
                    'name' => $this->getForeignName($key)
                ])
            );
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table index {name} has been added', [
                'name' => $this->getIndexName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table index {name} adding', [
                    'name' => $this->getIndexName($name)
                ])
            );
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been updated', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} updating', ['name' => $col])
            );
        }
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table {name} has been created', ['name' => $this->rawTable]));
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
                return $this->returnWarning(Yii::t('podium/flash', 'Table {name} has been dropped', ['name' => $this->rawTable]));
            }
            return true;
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table {name} dropping', [
                    'name' => $this->rawTable
                ])
            );
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
            return $this->returnWarning(Yii::t('podium/flash', 'Table column {name} has been dropped', ['name' => $col]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} dropping', ['name' => $col])
            );
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
            return $this->returnWarning(Yii::t('podium/flash', 'Table foreign key {name} has been dropped', [
                'name' => $this->getForeignName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table foreign key {name} dropping', [
                    'name' => $this->getForeignName($name)
                ])
            );
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
            return $this->returnWarning(Yii::t('podium/flash', 'Table index {name} has been dropped', [
                'name' => $this->getIndexName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table index {name} dropping', [
                    'name' => $this->getIndexName($name)
                ])
            );
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table {name} has been renamed to {new}', [
                'name' => $this->rawTable,
                'new'  => $this->getTableName($name)
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table {name} renaming to {new}', [
                    'name' => $this->rawTable,
                    'new'  => $this->getTableName($name)
                ])
            );
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
            return $this->returnSuccess(Yii::t('podium/flash', 'Table column {name} has been renamed to {new}', [
                'name' => $col,
                'new'  => $name
            ]));
        } catch (Exception $e) {
            return $this->returnError($e->getMessage(), __METHOD__,
                Yii::t('podium/flash', 'Error during table column {name} renaming to {new}', [
                    'name' => $col,
                    'new'  => $name
                ])
            );
        }
    }

    /**
     * Returns prefixed foreign key name.
     * @param string $name
     * @return string
     */
    public function getForeignName($name)
    {
        return 'fk-' . $this->rawTable . '-' . (is_array($name) ? implode('_', $name) : $name);
    }

    /**
     * Returns prefixed index name.
     * @param string $name
     * @return string
     */
    public function getIndexName($name)
    {
        return 'idx-' . $this->rawTable . '-' . $name;
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
        return $this->_prefix . $this->_table;
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
        return '{{%' . $this->_prefix . $name . '}}';
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
     * Returns success message.
     * Sets type to success.
     * @param string $message
     * @return string
     */
    public function returnSuccess($message)
    {
        $this->type = self::TYPE_SUCCESS;
        return $message;
    }

    /**
     * Returns success message.
     * Sets type to warning.
     * @param string $message
     * @return string
     */
    public function returnWarning($message)
    {
        $this->type = self::TYPE_WARNING;
        return $message;
    }

    /**
     * Returns error message.
     * Logs error.
     * @param string $exception exception message
     * @param string $method method name
     * @param string $message custom message
     * @return string
     */
    public function returnError($exception, $method, $message)
    {
        Yii::error($exception, $method);
        return $message . ':' . Html::tag('pre', $exception);
    }
}
