<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use bizley\podium\models\Post;
use Exception;
use Yii;
use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\Html;
use yii\rbac\BaseManager;

/**
 * Podium Maintenance module
 * Maintenance requires database connection to be configured first.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * 
 * @property \yii\rbac\BaseManager $authManager Authorization Manager
 * @property \yii\db\Connection $db Database connection
 */
class Maintenance extends Component
{

    /**
     * @var BaseManager authorization manager.
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
     * @var int number of all steps.
     */
    protected $_numberOfSteps;
    
    /**
     * @var int current percent.
     */
    protected $_percent;
    
    /**
     * @var string Podium database prefix.
     */
    protected $_prefix = 'podium_';
    
    /**
     * @var string current step result.
     */
    protected $_result = '';
    
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
    protected function _addColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column type missing.'));
        }
        try {
            $this->db->createCommand()->addColumn($this->getTable(), $data['col'], $data['type'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been added', ['name' => $data['col']]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table column {name} adding', ['name' => $data['col']]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Updates database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _alterColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['type']) || !is_string($data['type'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column type missing.'));
        }
        try {
            $this->db->createCommand()->alterColumn($this->getTable(), $data['col'], $data['type'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been updated', ['name' => $data['col']]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table column {name} updating', ['name' => $data['col']]) . ': ' .
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
            return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been created', ['name' => $this->db->getSchema()->getRawTableName($this->getTable())]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table {name} creating', ['name' => $this->getTable()]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Drops database table if it exists.
     * @return string result message.
     */
    protected function _drop()
    {
        try {
            if ($this->db->schema->getTableSchema($this->getTable(), true) !== null) {
                $this->db->createCommand()->dropTable($this->getTable())->execute();
                return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been dropped.', ['name' => $this->getTable()]));
            }
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table {name} dropping', ['name' => $this->getTable()]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Drops database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _dropColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        try {
            $this->db->createCommand()->dropColumn($this->getTable(), $data['col'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table column {name} has been dropped', ['name' => $data['col']]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table column {name} dropping', ['name' => $data['col']]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Drops database table foreign key.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _dropForeign($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Foreign key name missing.'));
        }
        try {
            $this->db->createCommand()->dropForeignKey($this->getForeignName($data['name']), $this->getTable())->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been dropped', ['name' => $this->getForeignName($data['name'])]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table foreign key {name} dropping', ['name' => $this->getForeignName($data['name'])]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Drops database table index.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _dropIndex($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Index name missing.'));
        }
        try {
            $this->db->createCommand()->dropIndex($this->getIndexName($data['name']), $this->getTable())->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index {name} has been dropped', ['name' => $this->getIndexName($data['name'])]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table index {name} dropping', ['name' => $this->getIndexName($data['name'])]) . ': ' .
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
                    $this->getTable(), 
                    $data['key'], 
                    $this->getTableName($data['ref']), 
                    $data['col'],
                    !empty($data['delete']) ? $data['delete'] : null,
                    !empty($data['update']) ? $data['update'] : null
                )->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table foreign key {name} has been added', ['name' => $this->getForeignName($data['key'])]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table foreign key {name} adding', ['name' => $this->getForeignName($data['key'])]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
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
            return $this->outputSuccess(Yii::t('podium/flash', 'Table index {name} has been added', ['name' => $this->getIndexName($data['name'])]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table index {name} adding', ['name' => $this->getIndexName($data['name'])]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Renames database table.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _rename($data)
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! New table name missing.'));
        }
        try {
            $this->db->createCommand()->renameTable($this->getTable(), $this->getTableName($data['name']))->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Table {name} has been renamed to {new}', ['name' => $this->getTable(), 'new' => $this->getTableName($data['name'])]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table {name} renaming to {new}', ['name' => $this->getTable(), 'new' => $this->getTableName($data['name'])]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Renames database table column.
     * @param array $data installation step data.
     * @return string result message.
     */
    protected function _renameColumn($data)
    {
        if (empty($data['col']) || !is_string($data['col'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! Column name missing.'));
        }
        if (empty($data['name']) || !is_string($data['name'])) {
            return $this->outputDanger(Yii::t('podium/flash', 'Installation aborted! New column name missing.'));
        }
        try {
            $this->db->createCommand()->renameColumn($this->getTable(), $data['col'], $data['name'])->execute();
            return $this->outputSuccess(Yii::t('podium/flash', 'Column {name} has been renamed to {new}', ['name' => $data['col'], 'new' => $data['name']]));
        }
        catch (Exception $e) {
            Yii::error([$e->getName(), $e->getMessage()], __METHOD__);
            $this->setError(true);
            return $this->outputDanger(Yii::t('podium/flash', 'Error during table {name} renaming to {new}', ['name' => $data['col'], 'new' => $data['name']]) . ': ' .
                            Html::tag('pre', $e->getMessage()));
        }
    }
    
    /**
     * Checks if User database table exists.
     * @return boolean wheter User database exists.
     */
    public static function check()
    {
        try {
            (new Post)->getTableSchema();
            return true;
        }
        catch (Exception $e) {
            Yii::warning('Podium post database table not found - preparing for installation', __METHOD__);
        }

        return false;
    }
    
    /**
     * Gets error flag.
     * @return boolean
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * Gets prefixed foreign key name.
     * @param string $name
     * @return string
     */
    public function getForeignName($name)
    {
        return 'fk-' . $this->_table . '-' . (is_array($name) ? implode('_', $name) : $name);
    }
    
    /**
     * Gets prefixed index name.
     * @param string $name
     * @return string
     */
    public function getIndexName($name)
    {
        return 'idx-' . $this->_table . '-' . $name;
    }
    
    /**
     * Counts number of installation steps.
     * @return int
     */
    public function getNumberOfSteps()
    {
        if ($this->_numberOfSteps === null) {
            $this->_numberOfSteps = count(static::steps());
        }
        return $this->_numberOfSteps;
    }
    
    /**
     * Gets percent.
     * @return int
     */
    public function getPercent()
    {
        return $this->_percent;
    }
    
    /**
     * Gets step result.
     * @return string
     */
    public function getResult()
    {
        return $this->_result;
    }
    
    /**
     * Gets table name.
     * @return string
     */
    public function getTable()
    {
        return $this->_table == '...' ? '...' : $this->getTableName($this->_table);
    }
    
    /**
     * Gets prefixed table name.
     * @param string $name
     * @return string
     */
    public function getTableName($name)
    {
        return '{{%' . $this->_prefix . $name . '}}';
    }
    
    /**
     * Gets table options string.
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
            $this->authManager = Instance::ensure($this->authManager, BaseManager::className());
        }
        catch (Exception $e) {
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
    public function setNumberOfSteps()
    {
        throw new Exception('Don\'t set installation steps counter directly!');
    }
    
    /**
     * Sets percent.
     * @param int $value
     */
    public function setPercent($value)
    {
        $this->_percent = (int)$value;
    }
    
    /**
     * Sets step result.
     * @param string $value
     */
    public function setResult($value)
    {
        $this->_result = $value;
    }
    
    /**
     * Sets table name.
     * @param type $value
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
     */
    public static function steps()
    {
        return [];
    }
}