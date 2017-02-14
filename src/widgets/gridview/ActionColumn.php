<?php

namespace bizley\podium\widgets\gridview;

use Yii;
use yii\grid\ActionColumn as YiiActionColumn;
use yii\helpers\Html;

/**
 * Podium ActionColumn
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class ActionColumn extends YiiActionColumn
{
    /**
     * @var array the HTML attributes for the header cell tag.
     */
    public $headerOptions = ['class' => 'text-right'];
    /**
     * @var array|\Closure the HTML attributes for the data cell tag. This can either be an array of
     * attributes or an anonymous function ([[Closure]]) that returns such an array.
     * The signature of the function should be the following: `function ($model, $key, $index, $column)`.
     * Where `$model`, `$key`, and `$index` refer to the model, key and index of the row currently being rendered
     * and `$column` is a reference to the [[Column]] object.
     * A function may be used to assign different attributes to different rows based on the data in that row.
     */
    public $contentOptions = ['class' => 'text-right'];
    /**
     * @var array html options to be applied to the [[initDefaultButtons()|default buttons]].
     */
    public $buttonOptions = [
        'class' => 'btn btn-default btn-xs',
        'data-pjax' => '0',
        'data-toggle' => 'tooltip',
        'data-placement' => 'top',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->header = Yii::t('podium/view', 'Actions');
        $this->grid->view->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
    }

    /**
     * Returns button options.
     * @param array $options override
     * @return array
     */
    public static function buttonOptions($options)
    {
        return array_merge(
            [
                'class' => 'btn btn-default btn-xs',
                'data-pjax' => '0',
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
            ],
            $options
        );
    }

    /**
     * Returns muted button HTML.
     * @param string $icon class
     * @return string
     */
    public static function mutedButton($icon)
    {
        return Html::a(Html::tag('span', '', ['class' => $icon]), '#', ['class' => 'btn btn-xs disabled text-muted']);
    }
}
