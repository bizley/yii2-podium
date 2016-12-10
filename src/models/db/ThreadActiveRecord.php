<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use bizley\podium\helpers\Helper;
use bizley\podium\models\Forum;
use bizley\podium\models\Poll;
use bizley\podium\models\Post;
use bizley\podium\models\Subscription;
use bizley\podium\models\ThreadView;
use bizley\podium\models\User;
use bizley\podium\Podium;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\HtmlPurifier;

/**
 * Thread AR
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $category_id
 * @property int $forum_id
 * @property int $author_id
 * @property int $pinned
 * @property int $locked
 * @property int $posts
 * @property int $views
 * @property int $updated_at
 * @property int $created_at
 * @property int $new_post_at
 * @property int $edited_post_at
 */
class ThreadActiveRecord extends ActiveRecord
{
    /**
     * @var string attached post content
     */
    public $post;
    
    /**
     * @var bool thread subscription flag
     */
    public $subscribe;
    
    /**
     * @var int poll added
     * @since 0.5
     */
    public $poll_added = 0;
    
    /**
     * @var string poll question
     * @since 0.5
     */
    public $poll_question;
    
    /**
     * @var int number of possible poll votes
     * @since 0.5
     */
    public $poll_votes = 1;
    
    /**
     * @var string[] poll answers
     * @since 0.5
     */
    public $poll_answers = [];
    
    /**
     * @var string poll closing date
     * @since 0.5
     */
    public $poll_end;
    
    /**
     * @var int should poll results be hidden before voting
     * @since 0.5
     */
    public $poll_hidden = 0;

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
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
            ],
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
            ['post', 'filter', 'filter' => function ($value) {
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    return HtmlPurifier::process(strip_tags(trim($value)));
                }
                return HtmlPurifier::process(trim($value), Helper::podiumPurifierConfig('full'));
            }, 'on' => ['new']],
            ['pinned', 'boolean'],
            ['subscribe', 'boolean'],
            ['name', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(strip_tags(trim($value)));
            }],
            ['poll_question', 'string', 'max' => 255],
            ['poll_votes', 'integer', 'min' => 1, 'max' => 10],
            ['poll_answers', 'each', 'rule' => ['string', 'max' => 255]],
            ['poll_end', 'date', 'format' => 'yyyy-MM-dd'],
            [['poll_hidden', 'poll_added'], 'boolean'],
            ['poll_answers', 'requiredPollAnswers'],
            [['poll_question', 'poll_votes'], 'required', 'when' => function ($model) {
                return $model->poll_added;
            }, 'whenClient' => 'function (attribute, value) { return $("#poll_added").val() == 1; }'],
        ];
    }
    
    /**
     * Filters and validates poll answers.
     * @since 0.5
     */
    public function requiredPollAnswers()
    {
        if ($this->poll_added) {
            $this->poll_answers = array_unique($this->poll_answers);
            $filtered = [];
            foreach ($this->poll_answers as $answer) {
                if (!empty(trim($answer))) {
                    $filtered[] = trim($answer);
                }
            }
            $this->poll_answers = $filtered;
            if (count($this->poll_answers) < 2) {
                $this->addError('poll_answers', Yii::t('podium/view', 'You have to add at least 2 options.'));
            }
        }
    }
    
    /**
     * Returns poll attribute labels.
     * @return array
     * @since 0.5
     */
    public function attributeLabels()
    {
        return [
            'poll_question' => Yii::t('podium/view', 'Question'),
            'poll_votes' => Yii::t('podium/view', 'Number of votes'),
            'poll_hidden' => Yii::t('podium/view', 'Hide results before voting'),
            'poll_end' => Yii::t('podium/view', 'Poll ends at'),
        ];
    }

    /**
     * Forum relation.
     * @return Forum
     */
    public function getForum()
    {
        return $this->hasOne(Forum::className(), ['id' => 'forum_id']);
    }
    
    /**
     * Poll relation.
     * @return Forum
     * @since 0.5
     */
    public function getPoll()
    {
        return $this->hasOne(Poll::className(), ['thread_id' => 'id']);
    }

    /**
     * ThreadView relation for user.
     * @return ThreadView
     */
    public function getUserView()
    {
        return $this->hasOne(ThreadView::className(), ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }
    
    /**
     * ThreadView relation general.
     * @return ThreadView[]
     */
    public function getThreadView()
    {
        return $this->hasMany(ThreadView::className(), ['thread_id' => 'id']);
    }
    
    /**
     * Subscription relation.
     * @return Subscription
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }
    
    /**
     * Latest post relation.
     * @return Post
     */
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    /**
     * Posts count.
     * @return int
     * @since 0.2
     */
    public function getPostsCount()
    {
        return Post::find()->where(['thread_id' => $this->id])->count('id');
    }
    
    /**
     * First post relation.
     * @return Post
     */
    public function getPostData()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * First new not seen post relation.
     * @return Post
     */
    public function getFirstNewNotSeen()
    {
        return $this
                ->hasOne(Post::className(), ['thread_id' => 'id'])
                ->where(['>', 'created_at', $this->userView ? $this->userView->new_last_seen : 0])
                ->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * First edited not seen post relation.
     * @return Post
     */
    public function getFirstEditedNotSeen()
    {
        return $this
                ->hasOne(Post::className(), ['thread_id' => 'id'])
                ->where(['>', 'edited_at', $this->userView ? $this->userView->edited_last_seen : 0])
                ->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * Author relation.
     * @return User
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
}
