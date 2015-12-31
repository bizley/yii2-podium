<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\widgets;

use bizley\podium\models\Activity;
use bizley\podium\models\User;
use Yii;
use yii\base\Widget;
use yii\db\Expression;
use yii\helpers\Html;

/**
 * Podium Readers widget
 * Renders list of readers.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Readers extends Widget
{

    /**
     * @var string section
     */
    public $what;
    
    /**
     * Renders the list of users reading current section.
     * @return string
     */
    public function run()
    {
        $url = Yii::$app->request->getUrl();
        
        $out = '';
        
        switch ($this->what) {
            case 'forum':
                $out .= Yii::t('podium/view', 'Browsing this forum') . ': ';
                break;
            case 'topic':
                $out .= Yii::t('podium/view', 'Reading this thread') . ': ';
                break;
            case 'unread':
                $out .= Yii::t('podium/view', 'Browsing unread threads') . ': ';
                break;
            case 'members':
                $out .= Yii::t('podium/view', 'Browsing the members') . ': ';
                break;
        }

        $conditions = [
            'and',
            [Activity::tableName() . '.anonymous' => 0],
            ['is not', 'user_id', null],
            new Expression('`url` LIKE :url'),
            ['>=', Activity::tableName() . '.updated_at', time() - 5 * 60]
        ];
        
        $guest = true;
        $anon  = false;
        if (!Yii::$app->user->isGuest) {
            $guest = false;
            $me = User::findMe();
            $conditions[] = ['not in', 'user_id', $me->id];
            if ($me->anonymous == 0) {
                $out .= $me->podiumTag . ' ';
            }
            else {
                $anon = true;
            }
        }
        
        $users = Activity::find()->joinWith(['user'])->where($conditions)->params([':url' => $url . '%']);
        foreach ($users->each() as $user) {
            $out .= $user->user->podiumTag . ' ';
        }
        
        $conditions = [
            'and',
            ['anonymous' => 1],
            new Expression('`url` LIKE :url'),
            ['>=', 'updated_at', time() - 5 * 60]
        ];
        $anonymous = Activity::find()->where($conditions)->params([':url' => $url . '%'])->count('id');
        if ($anon) {
            $anonymous += 1;
        }
        
        $conditions = [
            'and',
            ['user_id' => null],
            new Expression('`url` LIKE :url'),
            ['>=', 'updated_at', time() - 5 * 60]
        ];
        $guests = Activity::find()->where($conditions)->params([':url' => $url . '%'])->count('id');
        if ($guest) {
            $guests += 1;
        }
            
        if ($anonymous) {
            $out .= Html::button(Yii::t('podium/view', '{n, plural, =1{# anonymous user} other{# anonymous users}}', ['n' => $anonymous]), ['class' => 'btn btn-xs btn-default disabled']) . ' ';
        }
        if ($guests) {
            $out .= Html::button(Yii::t('podium/view', '{n, plural, =1{# guest} other{# guests}}', ['n' => $guests]), ['class' => 'btn btn-xs btn-default disabled']);
        }
        
        return $out;
    }
}
