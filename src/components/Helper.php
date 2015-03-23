<?php

namespace bizley\podium\components;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use bizley\podium\models\User;

class Helper
{
    public static function sortOrder($attribute = null)
    {
        if (!empty($attribute)) {
            $sort = Yii::$app->request->get('sort');
            if ($sort == $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet']);
            }
            elseif ($sort == '-' . $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet-alt']);
            }
        }
        
        return null;
    }
    
    public static function roleLabel($role = null)
    {
        switch ($role) {
            case User::ROLE_ADMIN:
                $label = 'danger';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            case User::ROLE_MODERATOR:
                $label = 'primary';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            default:
                $label = 'success';
                $name = ArrayHelper::getValue(User::getRoles(), User::ROLE_MEMBER);
        }
        
        return Html::tag('span', Yii::t('podium/view', $name), ['class' => 'label label-' . $label]);
    }
}