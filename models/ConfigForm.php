<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Config;
use yii\base\Model;
use yii\validators\StringValidator;

/**
 * ConfigForm model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class ConfigForm extends Model
{

    /**
     * @var Config Config instance.
     */
    public $config;
    
    /**
     * @var string[] Saved settings. 
     */
    public $settings;
    
    /**
     * @var string[] List of read-only settings. 
     */
    public $readonly = ['version'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->config   = Config::getInstance();
        $this->settings = $this->config->all();
    }
    
    /**
     * Returns the value of saved setting.
     * @param string $name Name of setting.
     * @return string
     */
    public function __get($name)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : '';
    }
    
    /**
     * Updates the value of setting.
     * @param string[] $data
     * @return boolean
     */
    public function update($data)
    {
        $validator = new StringValidator();
        $validator->max = 255;
        
        foreach ($data as $key => $value) {
            if (!in_array($key, $this->readonly) && isset($this->settings[$key])) {
                if ($validator->validate($value)) {
                    if (!$this->config->set($key, $value)) {
                        return false;
                    }                            
                }
                else {
                    return false;
                }
            }
        }
        
        return true;
    }
}
