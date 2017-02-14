<?php

namespace bizley\podium\maintenance;

use bizley\podium\models\Post;
use Exception;
use yii\di\Instance;
use yii\rbac\DbManager;

/**
 * Podium Maintenance
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Maintenance extends SchemaOperation
{
    /**
     * @var DbManager authorization manager.
     */
    public $authManager;

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
     * Initialize component.
     */
    public function init()
    {
        parent::init();
        $this->authManager = Instance::ensure($this->module->rbac, DbManager::className());
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
     * Installation steps to be set.
     * This should be overriden.
     */
    public function getSteps()
    {
        throw new Exception('This method must be overriden in Installation and Update class!');
    }
}
