<?php

namespace bizley\podium\widgets;

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use cebe\gravatar\Gravatar;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Podium Avatar widget
 * Renders user avatar image for each post.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Avatar extends Widget
{
    /**
     * @var User|null Avatar owner.
     */
    public $author;
    
    /**
     * @var bool Whether user name should appear underneath the image.
     */
    public $showName = true;

    /**
     * Renders the image.
     * Based on user settings the avatar can be uploaded image, Gravatar image or default one.
     * @return string
     */
    public function run()
    {
        if ($this->author instanceof User) {
            $meta = $this->author->meta;
            if (empty($meta)) {
                $avatar = Html::img(Helper::defaultAvatar(), [
                    'class' => 'podium-avatar img-circle img-responsive center-block', 
                    'alt'   => Html::encode($this->author->podiumName)
                ]);
            } else {
                if (!empty($meta->gravatar)) {
                    $avatar = Gravatar::widget([
                        'email'        => $this->author->email,
                        'defaultImage' => 'identicon',
                        'rating'       => 'r',
                        'options'      => [
                            'alt'   => Html::encode($this->author->podiumName),
                            'class' => 'podium-avatar img-circle img-responsive center-block',
                        ]
                    ]);
                } elseif (!empty($meta->avatar)) {
                    $avatar = Html::img('/avatars/' . $meta->avatar, [
                        'class' => 'podium-avatar img-circle img-responsive center-block', 
                        'alt'   => Html::encode($this->author->podiumName)
                    ]);
                }
            }
            $name = $this->showName ? $this->author->podiumTag : '';
        } else {
            $avatar = Html::img(Helper::defaultAvatar(), ['class' => 'podium-avatar img-circle img-responsive center-block', 'alt' => Yii::t('podium/view', 'user deleted')]);
            $name   = $this->showName ? Helper::deletedUserTag(true) : '';
        }
        
        $name = $this->showName ? Html::tag('p', $name, ['class' => 'avatar-name']) : '';
        
        return $avatar . $name;
    }
}
