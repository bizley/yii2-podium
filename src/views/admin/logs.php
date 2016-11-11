<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\log\Log;
use bizley\podium\Podium;
use bizley\podium\widgets\gridview\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'logs']); ?>
<br>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'rowOptions' => function($model) {
        switch ($model->level) {
            case 1:  $class = 'danger';  break;
            case 2:  $class = 'warning'; break;
            default: $class = '';
        }
        return ['class' => $class];
    },
    'columns' => [
        [
            'attribute' => 'id',
            'label' => Yii::t('podium/view', 'ID'),
        ],
        [
            'attribute' => 'level',
            'label' => Yii::t('podium/view', 'Level'),
            'encodeLabel' => false,
            'filter' => Log::getTypes(),
            'format' => 'raw',
            'value' => function ($model) {
                $name  = ArrayHelper::getValue(Log::getTypes(), $model->level, 'other');
                switch ($model->level) {
                    case 1:  $class = 'danger';  break;
                    case 2:  $class = 'warning'; break;
                    case 4:  $class = 'info';    break;
                    default: $class = 'default';
                }
                return Html::tag('span', $name, ['class' => 'label label-' . $class]);
            },
        ],
        [
            'attribute' => 'category',
            'label' => Yii::t('podium/view', 'Category'),
            'value' => function ($model) {
                return str_replace('bizley\podium', '', $model->category);
            },
        ],
        [
            'attribute' => 'log_time',
            'label' => Yii::t('podium/view', 'Time'),
            'filter' => false,
            'value' => function ($model) {
                return Podium::getInstance()->formatter->asDatetime(floor($model->log_time), 'medium');
            },
        ],
        [
            'attribute' => 'ip',
            'label' => Yii::t('podium/view', 'IP'),
        ],
        [
            'attribute' => 'message',
            'label' => Yii::t('podium/view', 'Message'),
            'format' => 'raw',
            'value' => function ($model) {
                return nl2br(Html::encode($model->message));
            },
        ],
        [
            'attribute' => 'model',
            'label' => Yii::t('podium/view', 'Model ID'),
            'value' => function ($model) {
                return $model->model !== null ? $model->model : '';
            },
        ],
        [
            'attribute' => 'user',
            'label' => Yii::t('podium/view', 'Who'),
            'value' => function ($model) {
                return $model->user !== null ? $model->user : '';
            },
        ],
    ],
]);
