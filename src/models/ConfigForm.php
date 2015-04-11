<?php

namespace bizley\podium\models;

use bizley\podium\components\Config;
use yii\base\Model;
use yii\validators\StringValidator;

class ConfigForm extends Model
{

    public $config;
    public $settings;
    public $readonly = ['version'];

    public function init()
    {
        parent::init();

        $this->config   = Config::getInstance();
        $this->settings = $this->config->all();
    }
    
    public function __get($name)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : '';
    }
    
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
                else return false;
            }
        }
        
        return true;
    }

}