<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\widgets;

use bizley\podium\components\Helper;
use bizley\podium\components\PodiumUser;
use cebe\gravatar\Gravatar;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Podium Avatar widget
 * Renders user avatar image for each post.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Avatar extends Widget
{

    /**
     * @var PodiumUser|null Avatar owner.
     */
    public $author;
    
    /**
     * @var boolean Wheter user name should appear underneath the image.
     */
    public $showName = true;

    /**
     * Renders the image.
     * Based on user settings the avatar can be uploaded image, Gravatar image or default one.
     * @return string
     */
    public function run()
    {
        if ($this->author instanceof PodiumUser) {

            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getName())]);
            $meta = $this->author->meta;
            if ($meta) {
                if (!empty($meta->gravatar)) {
                    $avatar = Gravatar::widget([
                        'email'        => $this->author->getEmail(),
                        'defaultImage' => 'identicon',
                        'rating'       => 'r',
                        'options'      => [
                            'alt'   => Html::encode($this->author->getName()),
                            'class' => 'podium-avatar img-circle img-responsive center-block',
                        ]
                    ]);
                }
                elseif (!empty($meta->avatar)) {
                    $avatar = Html::img('/avatars/' . $meta->avatar, ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Html::encode($this->author->getName())]);
                }
            }
            $name = $this->showName ? $this->author->getTag() : '';
        }
        else {
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Yii::t('podium/view', 'User deleted')]);
            $name = $this->showName ? Helper::deletedUserTag(true) : '';
        }
        
        $name = $this->showName ? Html::tag('p', $name, ['class' => 'avatar-name']) : '';
        
        return $avatar . $name;
    }
}
