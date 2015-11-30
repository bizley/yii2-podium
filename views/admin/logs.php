<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\log\Log;
use bizley\podium\widgets\PageSizer;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Logs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'logs']); ?>
<br>
<?php if (YII_ENV_DEV): ?>
<p class="text-danger"><span class="glyphicon glyphicon-alert"></span> <?= Yii::t('podium/view', 'Podium logging may be disabled in development environment (i.e. by Yii Debugger).') ?></p>
<?php endif; ?>

<?php Pjax::begin(); ?>
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'filterModel'    => $searchModel,
    'filterSelector' => 'select#per-page',
    'tableOptions'   => ['class' => 'table table-striped table-hover'],
    'rowOptions'     => function($model) {
        switch ($model->level) {
            case 1:  $class = 'danger';  break;
            case 2:  $class = 'warning'; break;
            default: $class = '';
        }
        return ['class' => $class];
    },
    'columns' => [
        [
            'attribute'   => 'id',
            'label'       => Yii::t('podium/view', 'ID') . Helper::sortOrder('id'),
            'encodeLabel' => false,
        ],
        [
            'attribute'   => 'level',
            'label'       => Yii::t('podium/view', 'Level') . Helper::sortOrder('level'),
            'encodeLabel' => false,
            'filter'      => Log::getTypes(),
            'format'      => 'raw',
            'value'       => function ($model) {
                $name  = ArrayHelper::getValue(Log::getTypes(), $model->level, 'other');
                switch ($model->level) {
                    case 1:  $class = 'danger';  break;
                    case 2:  $class = 'warning'; break;
                    case 4:  $class = 'info';    break;
                    default: $class = 'default';
                }
                return Html::tag('span', Yii::t('podium/view', $name), ['class' => 'label label-' . $class]);
            },
        ],
        [
            'attribute'   => 'category',
            'label'       => Yii::t('podium/view', 'Category') . Helper::sortOrder('category'),
            'encodeLabel' => false,
            'value'       => function ($model) {
                return str_replace('bizley\podium', '', $model->category);
            },
        ],
        [
            'attribute'   => 'log_time',
            'label'       => Yii::t('podium/view', 'Time') . Helper::sortOrder('log_time'),
            'encodeLabel' => false,
            'filter'      => false,
            'value'       => function ($model) {
                return Yii::$app->formatter->asDatetime(floor($model->log_time), 'medium');
            },
        ],
        [
            'attribute'   => 'prefix',
            'label'       => Yii::t('podium/view', 'Signature') . Helper::sortOrder('prefix'),
            'encodeLabel' => false,
        ],
        [
            'attribute'   => 'message',
            'label'       => Yii::t('podium/view', 'Message') . Helper::sortOrder('message'),
            'encodeLabel' => false,
            'format'      => 'raw',
            'value'       => function ($model) {
                return nl2br(Html::encode($model->message));
            },
        ],
        [
            'attribute'   => 'model',
            'label'       => Yii::t('podium/view', 'Model ID') . Helper::sortOrder('model'),
            'encodeLabel' => false,
            'value'       => function ($model) {
                return $model->model !== null ? $model->model : '';
            },
        ],
        [
            'attribute'   => 'blame',
            'label'       => Yii::t('podium/view', 'Who') . Helper::sortOrder('blame'),
            'encodeLabel' => false,
            'value'       => function ($model) {
                return $model->blame !== null ? $model->blame : '';
            },
        ],
    ],
]); ?>
<?php Pjax::end();
