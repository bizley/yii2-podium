<?php

namespace bizley\podium\models\forms;

use bizley\podium\Podium;
use bizley\podium\PodiumConfig;
use yii\base\Model;
use yii\validators\StringValidator;

/**
 * ConfigForm model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class ConfigForm extends Model
{
    /**
     * @var PodiumConfig Configuration instance.
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
        $this->config = Podium::getInstance()->podiumConfig;
        $this->settings = $this->config->all;
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
     * @return bool
     */
    public function update($data)
    {
        $validator = new StringValidator();
        $validator->max = 255;

        foreach ($data as $key => $value) {
            if (!in_array($key, $this->readonly) && array_key_exists($key, $this->settings)) {
                if (!$validator->validate($value)) {
                    return false;
                }
                if (!$this->config->set($key, $value)) {
                    return false;
                }
            }
        }
        return true;
    }
}
