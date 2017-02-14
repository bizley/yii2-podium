<?php

namespace bizley\podium\widgets;

use bizley\podium\models\Activity;
use bizley\podium\models\User;
use bizley\podium\Podium;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Podium Readers widget
 * Renders list of readers.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Readers extends Widget
{
    /**
     * @var string section
     */
    public $what;

    /**
     * @var bool anonymous user flag.
     * @since 0.2
     */
    protected $_anon = false;

    /**
     * @var bool guest user flag.
     * @since 0.2
     */
    protected $_guest = true;

    /**
     * Returns formatted list of named users browsing given url group.
     * @param string $url
     * @return string
     * @since 0.2
     */
    public function getNamedUsersList($url)
    {
        $out = '';
        $conditions = ['and',
            [Activity::tableName() . '.anonymous' => 0],
            ['is not', 'user_id', null],
            ['like', 'url', $url . '%', false],
            ['>=', Activity::tableName() . '.updated_at', time() - 5 * 60]
        ];

        if (!Podium::getInstance()->user->isGuest) {
            $this->_guest = false;
            $me = User::findMe();
            $conditions[] = ['!=', 'user_id', $me->id];
            if (!empty($me->meta) && $me->meta->anonymous == 0) {
                $out .= $me->podiumTag . ' ';
            } else {
                $this->_anon = true;
            }
        }

        $users = Activity::find()
                    ->joinWith(['user'])
                    ->where($conditions);
        foreach ($users->each() as $user) {
            $out .= $user->user->podiumTag . ' ';
        }

        return $out;
    }

    /**
     * Returns number of anonymous users browsing given url group.
     * @param string $url
     * @return int
     * @since 0.2
     */
    public function getAnonymousUsers($url)
    {
        $anons = Activity::find()
                    ->where(['and',
                        ['anonymous' => 1],
                        ['like', 'url', $url . '%', false],
                        ['>=', 'updated_at', time() - 5 * 60]
                    ])
                    ->count('id');
        if ($this->_anon) {
            $anons += 1;
        }

        return $anons;
    }

    /**
     * Returns number of guest users browsing given url group.
     * @param string $url
     * @return int
     * @since 0.2
     */
    public function getGuestUsers($url)
    {
        $guests = Activity::find()
                    ->where(['and',
                        ['user_id' => null],
                        ['like', 'url', $url . '%', false],
                        ['>=', 'updated_at', time() - 5 * 60]
                    ])
                    ->count('id');
        if ($this->_guest) {
            $guests += 1;
        }

        return $guests;
    }

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

        $out .= $this->getNamedUsersList($url);

        $anonymous = $this->getAnonymousUsers($url);
        if ($anonymous) {
            $out .= Html::button(
                Yii::t('podium/view', '{n, plural, =1{# anonymous user} other{# anonymous users}}', [
                    'n' => $anonymous
                ]),
                ['class' => 'btn btn-xs btn-default disabled']
            ) . ' ';
        }
        $guests = $this->getGuestUsers($url);
        if ($guests) {
            $out .= Html::button(
                Yii::t('podium/view', '{n, plural, =1{# guest} other{# guests}}', [
                    'n' => $guests
                ]),
                ['class' => 'btn btn-xs btn-default disabled']
            );
        }

        return $out;
    }
}
