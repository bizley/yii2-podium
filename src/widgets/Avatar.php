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
    public $showName = true;

    public function run()
    {
        if ($this->author instanceof User) {
            
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getPodiumName())]);
            $meta = $this->author->meta;
            if ($meta) {
                if (!empty($meta->gravatar)) {
                    $avatar = Gravatar::widget([
                        'email' => $this->author->email,
                        'defaultImage' => 'identicon',
                        'rating' => 'r',
                        'options' => [
                            'alt' => Html::encode($this->author->getPodiumName()),
                            'class' => 'podium-avatar img-circle img-responsive center-block',
                        ]
                    ]);
                }
                elseif (!empty($meta->avatar)) {
                    $avatar = Html::img('/avatars/' . $meta->avatar, ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getPodiumName())]);
                }
            }
            $name = $this->showName ? $this->author->getPodiumTag() : '';
            
        }
        else {
            
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Yii::t('podium/view', 'User deleted')]);
            $name = $this->showName ? Helper::deletedUserTag(true) : '';
        }
        
        $name = $this->showName ? Html::tag('p', $name, ['class' => 'avatar-name']) : '';
        
        return $avatar . $name;
    }

}