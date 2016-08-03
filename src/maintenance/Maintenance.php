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
 * @property boolean $error
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
    
    /**
     * @var integer number of all steps.
     */
    //protected $_numberOfSteps;
    
    /**
     * @var integer current percent.
     */
    //protected $_percent;
    
    /**
     * @var string Podium database prefix.
     */
    protected $_prefix = 'podium_';
    
    /**
     * @var string current step result.
     */
    //protected $_result = '';
    
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column type missing.'));
        }
        
        try {
            $this->db->createCommand()->addColumn($this->table, $data['col'], $data['type'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been added', [
                'name' => $data['col']
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table column {name} adding', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.'));
        }
        if (empty($data['ref']) || !is_string($data['ref'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Foreign key reference missing.'));
        }
        if (empty($data['col']) || (!is_string($data['col']) && !is_array($data['col']))) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Referenced columns missing.'));
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
            return $this->outputSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been added', [
                'name' => $this->getForeignName($data['key'])
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table foreign key {name} adding', [
                    'name' => $this->getForeignName($data['key'])
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index name missing.'));
        }
        if (empty($data['cols']) || !is_array($data['cols'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index columns missing.'));
        }
        
        try {
            $this->db->createCommand()->createIndex($this->getIndexName($data['name']), $this->table, $data['cols'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index {name} has been added', [
                'name' => $this->getIndexName($data['name'])
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table index {name} adding', [
                    'name' => $this->getIndexName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column type missing.'));
        }
        
        try {
            $this->db->createCommand()->alterColumn($this->table, $data['col'], $data['type'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been updated', [
                'name' => $data['col']
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table column {name} updating', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
        }
    }
    
    /**
     * Returns percent.
     * Clears cache at 100.
     * @param integer $currentStep
     * @param integer $maxStep
     * @return integer
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Database schema missing.'));
        }
        try {
            $this->db->createCommand()->createTable($this->table, $data['schema'], $this->getTableOptions())->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been created', [
                'name' => $this->db->schema->getRawTableName($this->table)
            ]));
        } catch (Exception $e) {
            if ($this->_table != 'log') {
                // in case of creating log table don't try to log error in it if it's not available
                Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            }
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table {name} creating', [
                    'name' => $this->table
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
        }
    }
    
    /**
     * Drops current database table if it exists.
     * @return string result message.
     */
    protected function dropTable()
    {
        try {
            if ($this->db->schema->getTableSchema($this->table, true) !== null) {
                $this->db->createCommand()->dropTable($this->table)->execute();
                return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been dropped', [
                    'name' => $this->table
                ]));
            }
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table {name} dropping', [
                    'name' => $this->table
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        
        try {
            $this->db->createCommand()->dropColumn($this->table, $data['col'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been dropped', [
                'name' => $data['col']
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table column {name} dropping', [
                    'name' => $data['col']
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.'));
        }
        
        try {
            $this->db->createCommand()->dropForeignKey($this->getForeignName($data['name']), $this->table)->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been dropped', [
                'name' => $this->getForeignName($data['name'])
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table foreign key {name} dropping', [
                    'name' => $this->getForeignName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index name missing.'));
        }
        
        try {
            $this->db->createCommand()->dropIndex($this->getIndexName($data['name']), $this->table)->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index {name} has been dropped', [
                'name' => $this->getIndexName($data['name'])
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table index {name} dropping', [
                    'name' => $this->getIndexName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! New table name missing.'));
        }
        
        try {
            $this->db->createCommand()->renameTable($this->table, $this->getTableName($data['name']))->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been renamed to {new}', [
                'name' => $this->table, 
                'new'  => $this->getTableName($data['name'])
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table {name} renaming to {new}', [
                    'name' => $this->table, 
                    'new'  => $this->getTableName($data['name'])
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
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
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! New column name missing.'));
        }
        
        try {
            $this->db->createCommand()->renameColumn($this->table, $data['col'], $data['name'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been renamed to {new}', [
                'name' => $data['col'], 
                'new'  => $data['name']
            ]));
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->error = true;
            return $this->outputDanger(
                Yii::t('podium/flash', 'Error during table column {name} renaming to {new}', [
                    'name' => $data['col'], 
                    'new'  => $data['name']
                ]) . ': ' . Html::tag('pre', $e->getMessage())
            );
        }
    }
    
    /**
     * Checks if Post database table exists.
     * This is taken as verification of Podium installation.
     * @return boolean whether Post database table exists.
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
     * Returns error flag.
     * @return boolean
     */
    public function getError()
    {
        return $this->_error;
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
     * Counts number of installation steps.
     * @return integer
     */
//    public function getNumberOfSteps()
//    {
//        if ($this->_numberOfSteps === null) {
//            $this->_numberOfSteps = count(static::steps());
//        }
//        return $this->_numberOfSteps;
//    }
    
    /**
     * Returns percent.
     * @return integer
     */
//    public function getPercent()
//    {
//        return $this->_percent;
//    }
    
    /**
     * Returns step result.
     * @return string
     */
//    public function getResult()
//    {
//        return $this->_result;
//    }
    
    /**
     * Returns table name.
     * @return string
     */
    public function getTable()
    {
        return $this->_table == '...' 
            ? '...' : $this->getTableName($this->_table);
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

        try {
            $this->db = Instance::ensure($this->db, Connection::className());
            if ($this->db->driverName === 'mysql') {
                $this->setTableOptions('CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
            }
            $this->authManager = Instance::ensure($this->authManager, DbManager::className());
        } catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
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
     * Prepares warning message.
     * @param string $content message content.
     * @return string prepared message.
     */
    public function outputWarning($content)
    {
        return Html::tag('span', $content, ['class' => 'text-warning']);
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
     * Sets error flag.
     * @param mixed $value
     */
    public function setError($value)
    {
        $this->_error = $value ? true : false;
    }
    
    /**
     * @throws Exception
     */
//    public function setNumberOfSteps()
//    {
//        throw new Exception('Don\'t set installation steps counter directly!');
//    }
    
    
    
    /**
     * Sets step result.
     * @param string $value
     */
//    public function setResult($value)
//    {
//        $this->_result = $value;
//    }
    
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
     * @throws Exception
     */
    public static function steps()
    {
        throw new Exception('This method must be overriden in Installation and Update class!');
    }
}
