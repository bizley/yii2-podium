<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\console;

use Exception;
use yii\console\Controller;

/**
 * Podium command line tool for installation and updating.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class InstallController extends Controller
{
    /**
     * Installs Podium.
     * @return integer|void
     */
    public function actionIndex()
    {
        try {
            $version = $this->module->version;
            $this->stdout("\nPodium Installation v{$version}\n");
            $this->stdout("-----------------------------------------------------------------------\n");
            
            $this->stdout("Podium will attempt to create all database tables required by the forum\n");
            $this->stdout("along with the default configuration and the administrator account.\n\n");
            $this->stdout("(!) Back up your existing database before installation.\n\n");
            
            $this->stdout("Installation options:\n");
            $this->stdout("1. Starts installation.\n");
            $this->stdout("2. (!) Drops all existing Podium databases and then starts installation.\n");
            $this->stdout("3. Quits installator.\n\n");
        
            $selection = $this->select("Select option:", [
                1 => 'Start Podium installation.',
                2 => 'Drop all existing Podium databases and then start installation.',
                3 => 'Quit'
            ]);
            
            switch ($selection) {
                case 1:
                    $this->install();
                    break;
                case 2:
                    $this->stdout("Starting Podium databases dropping...\n");
                    break;
                case 3:
                    $this->stdout("Bye bye\n");
            }
        } catch (Exception $e) {
            $this->stderr("ERROR: " . $e->getMessage() . "\n");
        }
    }
    
    public function install()
    {
        $this->stdout("Starting Podium installation...\n");
        
    }
}
