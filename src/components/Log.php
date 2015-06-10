<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use Yii;

/**
 * Log helper
 * .
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Log
{
    
    public static function blame()
    {
        if (!Yii::$app->user->isGuest) {
            return Yii::$app->user->id;
        }
        return null;
    }
    
    public static function error($msg, $model = null, $category = 'application')
    {
        Yii::error([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
    
    public static function getTypes()
    {
        return [
            1 => 'error',
            2 => 'warning',
            4 => 'info'
        ];
    }
    
    public static function info($msg, $model = null, $category = 'application')
    {
        Yii::info([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
    
    public static function warning($msg, $model = null, $category = 'application')
    {
        Yii::warning([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
}