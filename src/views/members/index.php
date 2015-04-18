<?php

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

$this->title                   = Yii::t('podium/view', 'Members List');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members List'), ['index']), ['role' => 'presentation', 'class' => 'active']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-scissors"></span> ' . Yii::t('podium/view', 'Moderation Team'), ['mods']), ['role' => 'presentation']);
echo Html::endTag('ul'); ?>

<br>

<?php Pjax::begin(); ?>
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'filterSelector' => 'select#per-page',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns'      => [
        [
            'attribute'          => 'username',
            'label'              => Yii::t('podium/view', 'Username') . Helper::sortOrder('username'),
            'encodeLabel'        => false,
            'format'             => 'raw',
            'value'              => function ($model) {
                return Html::a($model->getPodiumName(), ['view', 'id' => $model->id]);
            },
        ],
        [
            'attribute'          => 'role',
            'label'              => Yii::t('podium/view', 'Role') . Helper::sortOrder('role'),
            'encodeLabel'        => false,
            'filter'             => User::getRoles(),
            'value'              => function ($model) {
                return Yii::t('podium/view', ArrayHelper::getValue(User::getRoles(), $model->role));
            },
        ],
        [
            'attribute'          => 'created_at',
            'label'              => Yii::t('podium/view', 'Joined') . Helper::sortOrder('created_at'),
            'encodeLabel'        => false,
            'value'              => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{view} {pm}',
            'buttons'        => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Member')]);
                },
                'pm' => function($url, $model) {
                    if ($model->id !== Yii::$app->user->id) {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Send Message')]);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                    }
                },
            ],
        ]
    ],
]);

?>
<?php Pjax::end(); ?>