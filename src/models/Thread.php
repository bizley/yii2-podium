<?php

namespace bizley\podium\models;

use bizley\podium\components\Config;
use bizley\podium\components\Helper;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Thread model
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property integer $category_id
 * @property integer $forum_id
 * @property integer $author_id
 * @property integer $pinned
 * @property integer $updated_at
 * @property integer $created_at
 */
class Thread extends ActiveRecord
{

    const DESC_EDITED   = 'Edited Posts';
    const DESC_HOT      = 'Hot Thread';
    const DESC_NEW      = 'New Posts';
    const DESC_NO_NEW   = 'No New Posts';
    const DESC_LOCKED   = 'Locked Thread';
    const DESC_PINNED   = 'Pinned Thread';
    
    const CLASS_DEFAULT = 'default';
    const CLASS_EDITED  = 'warning';
    const CLASS_NEW     = 'success';
    
    const ICON_HOT      = 'fire';
    const ICON_LOCKED   = 'lock';
    const ICON_NEW      = 'leaf';
    const ICON_NO_NEW   = 'comment';
    const ICON_PINNED   = 'pushpin';

    public $post;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_thread}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class'     => SluggableBehavior::className(),
                'attribute' => 'name'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required', 'message' => Yii::t('podium/view', 'Topic can not be blank.')],
            ['post', 'required', 'on' => ['new']],
            ['post', 'string', 'min' => 10, 'on' => ['new']],
            ['post', 'filter', 'filter' => function($value) {
                    return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
                }, 'on' => ['new']],
            ['pinned', 'boolean'],
            ['name', 'validateName'],
        ];
    }

    /**
     * Validates name
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateName($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\w\s\p{L}]{1,255}$/u', $this->name)) {
                $this->addError($attribute, Yii::t('podium/view', 'Name must contain only letters, digits, underscores and spaces (255 characters max).'));
            }
        }
    }

    public function getView()
    {
        return $this->hasOne(ThreadView::className(), ['thread_id' => 'id'])->where(['user_id' => Yii::$app->user->id]);
    }
    
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    public function search($forum_id = null)
    {
        $query = self::find();
        if ($forum_id) {
            $query->where(['forum_id' => (int) $forum_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        return $dataProvider;
    }

    public function getIcon()
    {
        $icon   = self::ICON_NO_NEW;
        $append = false;

        if ($this->locked) {
            $icon   = self::ICON_LOCKED;
            $append = true;
        }
        elseif ($this->pinned) {
            $icon   = self::ICON_PINNED;
            $append = true;
        }
        elseif ($this->posts >= Config::getInstance()->get('hot_minimum')) {
            $icon   = self::ICON_HOT;
            $append = true;
        }

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            }
        }
        else {
            if (!$append) {
                $icon = self::ICON_NEW;
            }
        }

        return $icon;
    }

    public function getDescription()
    {
        $description = self::DESC_NO_NEW;
        $append      = false;

        if ($this->locked) {
            $description = self::DESC_LOCKED;
            $append      = true;
        }
        elseif ($this->pinned) {
            $description = self::DESC_PINNED;
            $append      = true;
        }
        elseif ($this->posts >= Config::getInstance()->get('hot_minimum')) {
            $description = self::DESC_HOT;
            $append      = true;
        }

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                if (!$append) {
                    $description = self::DESC_NEW;
                }
                else {
                    $description .= ' (' . self::DESC_NEW . ')';
                }
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                if (!$append) {
                    $description = self::DESC_EDITED;
                }
                else {
                    $description = ' (' . self::DESC_EDITED . ')';
                }
            }
        }
        else {
            if (!$append) {
                $description = self::DESC_NEW;
            }
            else {
                $description .= ' (' . self::DESC_NEW . ')';
            }
        }

        return $description;
    }

    public function getClass()
    {
        $class = self::CLASS_DEFAULT;

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                $class = self::CLASS_NEW;
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                $class = self::CLASS_EDITED;
            }
        }
        else {
            $class = self::CLASS_NEW;
        }

        return $class;
    }
}