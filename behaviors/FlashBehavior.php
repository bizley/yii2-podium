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
 * Prepares messages for \bizley\podium\widgets\Alert widget.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class FlashBehavior extends Behavior
{
    /**
     * Alias for warning().
     * @param string $message the flash message to be translated.
     */
    public function alert($message)
    {
        Yii::$app->session->addFlash('warning', $message);
    }
    
    /**
     * Adds flash message of 'danger' type.
     * @param string $message the flash message to be translated.
     */
    public function danger($message)
    {
        Yii::$app->session->addFlash('danger', $message);
    }
    
    /**
     * Alias for danger().
     * @param string $message the flash message to be translated.
     */
    public function error($message)
    {
        Yii::$app->session->addFlash('danger', $message);
    }
    
    /**
     * Adds flash message of given type.
     * @param string $type the type of flash message.
     * @param string $message the flash message to be translated.
     */
    public function goFlash($type, $message)
    {
        Yii::$app->session->addFlash($type, $message);
    }
    
    /**
     * Adds flash message of 'info' type.
     * @param string $message the flash message to be translated.
     */
    public function info($message)
    {
        Yii::$app->session->addFlash('info', $message);
    }
    
    /**
     * Alias for success().
     * @param string $message the flash message to be translated.
     */
    public function ok($message)
    {
        Yii::$app->session->addFlash('success', $message);
    }
    
    /**
     * Adds flash message of 'success' type.
     * @param string $message the flash message to be translated.
     */
    public function success($message)
    {
        Yii::$app->session->addFlash('success', $message);
    }
    
    /**
     * Adds flash message of 'warning' type.
     * @param string $message the flash message to be translated.
     */
    public function warning($message)
    {
        Yii::$app->session->addFlash('warning', $message);
    } 
}
