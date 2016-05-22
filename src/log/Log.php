<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\log;

use bizley\podium\models\User;
use Yii;
use yii\web\Application;

/**
 * Log helper
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Log
{
    
    /**
     * Returns ID of user responsible for logged action.
     * @return integer|null
     */
    public static function blame()
    {
        if (Yii::$app instanceof Application && !Yii::$app->user->isGuest) {
            return User::loggedId();
        }
        return null;
    }
    
    /**
     * Calls for error log.
     * @param mixed $msg Message
     * @param string $model Model
     * @param string $category
     */
    public static function error($msg, $model = null, $category = 'application')
    {
        Yii::error([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
    
    /**
     * Returns log types.
     * @return array
     */
    public static function getTypes()
    {
        return [
            1 => Yii::t('podium/view', 'error'),
            2 => Yii::t('podium/view', 'warning'),
            4 => Yii::t('podium/view', 'info')
        ];
    }
    
    /**
     * Calls for info log.
     * @param mixed $msg Message
     * @param string $model Model
     * @param string $category
     */
    public static function info($msg, $model = null, $category = 'application')
    {
        Yii::info([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
    
    /**
     * Calls for warning log.
     * @param mixed $msg Message
     * @param string $model Model
     * @param string $category
     */
    public static function warning($msg, $model = null, $category = 'application')
    {
        Yii::warning([
            'msg'   => $msg,
            'model' => $model,
            'blame' => self::blame(),
        ], $category);
    }
}
