<?php

namespace bizley\podium\models;

use yii\base\Model;
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
                return HtmlPurifier::process($value);
            }],
            [['match'], 'in', 'range' => ['all', 'any']],
            [['date_from', 'date_to'], 'default', 'value' => null],
            [['date_from', 'date_to'], 'date'],
            [['forums'], 'safe'],
            [['type', 'display'], 'in', 'range' => ['posts', 'topics']],
        ];
    }

    
}