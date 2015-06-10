<?php

namespace bizley\podium\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\HtmlPurifier;

class SearchForm extends Model
{

    public $query;
    public $match;
    public $author;
    public $date_from;
    public $date_to;
    public $forums;
    public $type;
    public $display;

    public function rules()
    {
        return [
            [['query', 'author'], 'string'],
            [['query', 'author'], 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(trim($value));
            }],
            ['query', 'string', 'min' => 3],
            ['author', 'string', 'min' => 2],
            [['match'], 'in', 'range' => ['all', 'any']],
            [['date_from', 'date_to'], 'default', 'value' => null],
            ['date_from', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'date_from'],
            ['date_to', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'date_to'],
            [['forums'], 'safe'],
            [['type', 'display'], 'in', 'range' => ['posts', 'topics']],
        ];
    }

    // TODO: ograniczenia widocznosci forum
    public function searchAdvanced()
    {
        if ($this->type == 'topics') {
            $query = Thread::find();
            if (!empty($this->query)) {
                $words = explode(' ', $this->query);
                foreach ($words as $word) {
                    if ($this->match == 'all') {
                        $query->andWhere(['like', 'name', $word]);
                    }
                    else {
                        $query->orWhere(['like', 'name', $word]);
                    }
                }
            }
            if (!empty($this->author)) {
                $query->andWhere(['like', 'username', $this->author])->joinWith(['author']);
            }
            if (!empty($this->date_from) && empty($this->date_to)) {
                $query->andWhere(['>=', Thread::tableName() . '.created_at', $this->date_from]);
            }
            elseif (!empty($this->date_to) && empty($this->date_from)) {
                $this->date_to += 23 * 3600 + 59 * 60 + 59; // 23:59:59
                $query->andWhere(['<=', Thread::tableName() . '.created_at', $this->date_to]);
            }
            elseif (!empty($this->date_to) && !empty($this->date_from)) {
                if ($this->date_from > $this->date_to) {
                    $tmp = $this->date_to;
                    $this->date_to   = $this->date_from;
                    $this->date_from = $tmp;
                }
                $this->date_to += 23 * 3600 + 59 * 60 + 59; // 23:59:59
                $query->andWhere(['<=', Thread::tableName() . '.created_at', $this->date_to]);
                $query->andWhere(['>=', Thread::tableName() . '.created_at', $this->date_from]);
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
            $sort = [
                'defaultOrder' => [Thread::tableName() . '.id' => SORT_DESC],
                'attributes' => [
                    Thread::tableName() . '.id' => [
                        'asc'     => [Thread::tableName() . '.id' => SORT_ASC],
                        'desc'    => [Thread::tableName() . '.id' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ]
            ];
        }
        else {
            $query = Vocabulary::find()->select('post_id, thread_id')->joinWith(['posts']);
            if (!empty($this->query)) {
                $words = explode(' ', $this->query);
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
            if (!empty($this->author)) {
                $query->andWhere(['like', 'username', $this->author])->joinWith(['posts' => function($q) {
                    $q->joinWith(['user']);
                }]);
            }
            if (!empty($this->date_from) && empty($this->date_to)) {
                $query->andWhere(['>=', Post::tableName() . '.updated_at', $this->date_from]);
            }
            elseif (!empty($this->date_to) && empty($this->date_from)) {
                $this->date_to += 23 * 3600 + 59 * 60 + 59; // 23:59:59
                $query->andWhere(['<=', Post::tableName() . '.updated_at', $this->date_to]);
            }
            elseif (!empty($this->date_to) && !empty($this->date_from)) {
                if ($this->date_from > $this->date_to) {
                    $tmp = $this->date_to;
                    $this->date_to   = $this->date_from;
                    $this->date_from = $tmp;
                }
                $this->date_to += 23 * 3600 + 59 * 60 + 59; // 23:59:59
                $query->andWhere(['<=', Post::tableName() . '.updated_at', $this->date_to]);
                $query->andWhere(['>=', Post::tableName() . '.updated_at', $this->date_from]);
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
            $sort = [
                'defaultOrder' => ['post_id' => SORT_DESC],
                'attributes' => [
                    'post_id' => [
                        'asc'     => ['post_id' => SORT_ASC],
                        'desc'    => ['post_id' => SORT_DESC],
                        'default' => SORT_DESC,
                    ],
                ]
            ];
        }       
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => $sort,
        ]);

        return $dataProvider;
    }
}