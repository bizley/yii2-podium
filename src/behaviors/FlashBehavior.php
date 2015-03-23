<?php
/*
 * @author Bizley
 */
namespace bizley\podium\behaviors;

use Yii;
use yii\base\Behavior;

class FlashBehavior extends Behavior
{
    public function danger($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->goFlash('danger', $category, $message, $params, $language);
    }
    
    public function success($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->goFlash('success', $category, $message, $params, $language);
    }
    
    public function info($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->goFlash('info', $category, $message, $params, $language);
    }
    
    public function warning($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->goFlash('warning', $category, $message, $params, $language);
    }
    
    public function error($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->danger($message, $params, $category, $language);
    }
    
    public function alert($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->warning($message, $params, $category, $language);
    }
    
    public function ok($message, $params = [], $category = 'podium/flash', $language = null)
    {
        return $this->success($message, $params, $category, $language);
    }
    
    public function goFlash($type, $category, $message, $params, $language)
    {
        Yii::$app->session->addFlash($type, Yii::t($category, $message, $params, $language));
    }
}