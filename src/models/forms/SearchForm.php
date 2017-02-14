<?php

namespace bizley\podium\models\forms;

use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Post;
use bizley\podium\models\Thread;
use bizley\podium\models\Vocabulary;
use bizley\podium\Podium;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;

/**
 * SearchForm model
 * Advanced forum search.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class SearchForm extends Model
{
    /**
     * @var string Query
     */
    public $query;

    /**
     * @var string Whether to search for all words or any word
     */
    public $match;

    /**
     * @var string Author name
     */
    public $author;

    /**
     * @var string Date from [yyyy-MM-dd]
     */
    public $dateFrom;

    /**
     * @var int Date from stamp
     * @ince 0.6
     */
    public $dateFromStamp;

    /**
     * @var string Date to [yyyy-MM-dd]
     */
    public $dateTo;

    /**
     * @var int Date to stamp
     * @since 0.6
     */
    public $dateToStamp;

    /**
     * @var string Whether to search for posts or topics
     */
    public $forums;

    /**
     * @var string Whether to search for posts or topics
     */
    public $type;

    /**
     * @var string Whether to display results as posts or topics
     */
    public $display;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['query', 'author'], 'string'],
            [['query', 'author'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim($value));
            }],
            ['query', 'string', 'min' => 3],
            ['author', 'string', 'min' => 2],
            [['match'], 'in', 'range' => ['all', 'any']],
            [['dateFrom', 'dateTo'], 'default', 'value' => null],
            ['dateFrom', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'dateFromStamp'],
            ['dateTo', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'dateToStamp'],
            [['dateFromStamp', 'dateToStamp'], 'integer'],
            [['forums'], 'each', 'rule' => ['integer']],
            [['type', 'display'], 'in', 'range' => ['posts', 'topics']],
        ];
    }

    /**
     * Prepares query conditions.
     * @param ActiveQuery $query
     * @param bool $topics
     * @since 0.6
     */
    protected function prepareQuery($query, $topics = false)
    {
        $field = $topics
                ? Thread::tableName() . '.created_at'
                : Post::tableName() . '.updated_at';
        if (!empty($this->author)) {
            $query->andWhere(['like', 'username', $this->author])->joinWith(['author']);
        }
        if (!empty($this->dateFromStamp) && empty($this->dateToStamp)) {
            $query->andWhere(['>=', $field, $this->dateFromStamp]);
        } elseif (!empty($this->dateToStamp) && empty($this->dateFromStamp)) {
            $this->dateToStamp += 23 * 3600 + 59 * 60 + 59; // 23:59:59
            $query->andWhere(['<=', $field, $this->dateToStamp]);
        } elseif (!empty($this->dateToStamp) && !empty($this->dateFromStamp)) {
            if ($this->dateFromStamp > $this->dateToStamp) {
                $tmp = $this->dateToStamp;
                $this->dateToStamp = $this->dateFromStamp;
                $this->dateFromStamp = $tmp;
            }
            $this->dateToStamp += 23 * 3600 + 59 * 60 + 59; // 23:59:59
            $query->andWhere(['<=', $field, $this->dateToStamp]);
            $query->andWhere(['>=', $field, $this->dateFromStamp]);
        }
        if (!empty($this->forums)) {
            if (is_array($this->forums)) {
                $forums = [];
                foreach ($this->forums as $f) {
                    if (is_numeric($f)) {
                        $forums[] = (int)$f;
                    }
                }
                if (!empty($forums)) {
                    $query->andWhere(['forum_id' => $forums]);
                }
            }
        }
    }

    /**
     * Advanced topics search.
     * @return ActiveDataProvider
     * @since 0.6
     */
    protected function searchTopics()
    {
        $query = Thread::find();
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['forum' => function ($q) {
                $q->andWhere([Forum::tableName() . '.visible' => 1])->joinWith(['category' => function ($q) {
                    $q->andWhere([Category::tableName() . '.visible' => 1]);
                }]);
            }]);
        }
        if (!empty($this->query)) {
            $words = explode(' ', preg_replace('/\s+/', ' ', $this->query));
            foreach ($words as $word) {
                if ($this->match == 'all') {
                    $query->andWhere(['like', Thread::tableName() . '.name', $word]);
                } else {
                    $query->orWhere(['like', Thread::tableName() . '.name', $word]);
                }
            }
        }
        $this->prepareQuery($query, true);
        $sort = [
            'defaultOrder' => [Thread::tableName() . '.id' => SORT_DESC],
            'attributes' => [
                Thread::tableName() . '.id' => [
                    'asc' => [Thread::tableName() . '.id' => SORT_ASC],
                    'desc' => [Thread::tableName() . '.id' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ]
        ];
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort,
        ]);
    }

    /**
     * Advanced posts search.
     * @return ActiveDataProvider
     * @since 0.6
     */
    public function searchPosts()
    {
        $query = Vocabulary::find()->select('post_id, thread_id')->joinWith(['posts.author', 'posts.thread'])->andWhere(['is not', 'post_id', null]);
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['posts.forum' => function ($q) {
                $q->andWhere([Forum::tableName() . '.visible' => 1])->joinWith(['category' => function ($q) {
                    $q->andWhere([Category::tableName() . '.visible' => 1]);
                }]);
            }]);
        }
        if (!empty($this->query)) {
            $words = explode(' ', preg_replace('/\s+/', ' ', $this->query));
            $countWords = 0;
            foreach ($words as $word) {
                $query->orWhere(['like', 'word', $word]);
                $countWords++;
            }
            $query->groupBy('post_id');
            if ($this->match == 'all' && $countWords > 1) {
                $query->select(['post_id', 'thread_id', 'COUNT(post_id) AS c'])->having(['>', 'c', $countWords - 1]);
            }
        }
        $this->prepareQuery($query);
        $sort = [
            'defaultOrder' => ['post_id' => SORT_DESC],
            'attributes' => [
                'post_id' => [
                    'asc' => ['post_id' => SORT_ASC],
                    'desc' => ['post_id' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ]
        ];
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => $sort,
        ]);
    }

    /**
     * Advanced search.
     * @return ActiveDataProvider
     */
    public function searchAdvanced()
    {
        if ($this->type == 'topics') {
            return $this->searchTopics();
        }
        return $this->searchPosts();
    }
}
