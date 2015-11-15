<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Content model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property string $name
 * @property string $topic
 * @property string $content
 */
class Content extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_content}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'topic'], 'required'],
            [['content', 'topic'], 'string', 'min' => 1],
            ['topic', 'filter', 'filter' => function($value) { return HtmlPurifier::process($value); }],
            ['content', 'filter', 'filter' => function($value) { return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full')); }],
        ];
    }
}