<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\behaviors;

use Yii;
use yii\base\Behavior;

/**
 * Podium Flash Behavior
 * Simplifies flash messages adding. Every message is automatically translated.
 * Prepares messages for [[\bizley\podium\widgets\Alert|Alert]] widget.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class FlashBehavior extends Behavior
{
    /**
     * Alias for [[warning()]].
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function alert($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->warning($message, $params, $category, $language);
    }
    
    /**
     * Adds flash message of 'danger' type.
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function danger($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->goFlash('danger', $category, $message, $params, $language);
    }
    
    /**
     * Alias for [[danger()]].
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function error($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->danger($message, $params, $category, $language);
    }
    
    /**
     * Adds flash message of given type.
     * @param string $type the type of flash message.
     * @param string $category the message category.
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function goFlash($type, $category, $message, $params, $language)
    {
        Yii::$app->session->addFlash($type, Yii::t($category, $message, $params, $language));
    }
    
    /**
     * Adds flash message of 'info' type.
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function info($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->goFlash('info', $category, $message, $params, $language);
    }
    
    /**
     * Alias for [[success()]].
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function ok($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->success($message, $params, $category, $language);
    }
    
    /**
     * Adds flash message of 'success' type.
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function success($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->goFlash('success', $category, $message, $params, $language);
    }
    
    /**
     * Adds flash message of 'warning' type.
     * @param string $message the flash message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $category the message category.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     */
    public function warning($message, $params = [], $category = 'podium/flash', $language = null)
    {
        $this->goFlash('warning', $category, $message, $params, $language);
    } 
}