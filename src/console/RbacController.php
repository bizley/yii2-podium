<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\console;

use bizley\podium\rbac\Rbac;
use Exception;
use yii\console\Controller;
use yii\di\Instance;
use yii\rbac\DbManager;

/**
 * Podium command line tool to add RBAC rules.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class RbacController extends Controller
{

    /**
     * @var DbManager authorization manager.
     */
    public $authManager = 'authManager';
    
    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['authManager']
        );
    }
    
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the authManager components.
     * @param \yii\base\Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        try {
            if (parent::beforeAction($action)) {
                $this->authManager = Instance::ensure($this->authManager, DbManager::className());
                return true;
            }
        }
        catch (Exception $e) {
            $this->stderr("ERROR: " . $e->getMessage() . "\n");
        }
        return false;
    }

    /**
     * Adds RBAC rules.
     * @return integer|void
     */
    public function actionIndex()
    {
        try {
            $version = $this->module->version;
            $this->stdout("\nPodium RBAC rules v{$version}\n");
            $this->stdout("------------------------------\n");

            $this->stdout("Set of Podium RBAC rules will be added to the authManager storage.\n\n");
            $this->stdout("* The same rules are added during installation process so if you are\n");
            $this->stdout("* planning to run Podium installation there is no need to add rules\n");
            $this->stdout("* now.\n\n");
        
            if ($this->confirm("Are you sure you want to add Podium RBAC rules?", true)) {
                $this->stdout("Adding RBAC rules... ");
                $this->addRules();
                $this->stdout("DONE\n");
            }
            else {
                $this->stdout("RBAC rules have not been added.\n");
            }
        }
        catch (Exception $e) {
            $this->stderr("ERROR: " . $e->getMessage() . "\n");
        }
    }
    
    /**
     * RBAC rules.
     */
    public function addRules()
    {
        (new Rbac)->add($this->authManager);
    }
}
