<?php

namespace bizley\podium\widgets;

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use cebe\gravatar\Gravatar;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Avatar extends Widget
{

    public $author;

    public function run()
    {
        if ($this->author instanceof User) {
            
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getPodiumName())]);
            $meta = $this->author->meta;
            if ($meta) {
                if (!empty($meta->gravatar)) {
                    $avatar = Gravatar::widget([
                        'email' => $this->author->email,
                        'defaultImage' => 'identicon',
                        'rating' => 'r',
                        'options' => [
                            'alt' => Html::encode($this->author->getPodiumName()),
                            'class' => 'img-circle img-responsive center-block',
                        ]
                    ]);
                }
                elseif (!empty($meta->avatar)) {
                    $avatar = Html::img('/avatars/' . $meta->avatar, ['class' => 'img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getPodiumName())]);
                }
            }
            $name = $this->author->getPodiumTag();
            
        }
        else {
            
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'img-circle img-responsive center-block', 'alt' => Yii::t('podium/view', 'User deleted')]);
            $name = Helper::deletedUserTag(true);
        }
        
        $name = Html::tag('p', $name, ['class' => 'avatar-name']);
        
        return $avatar . $name;
    }

}