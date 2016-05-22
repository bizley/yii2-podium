<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Subscriptions');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'subscriptions']) ?>
    </div>
    <div class="col-sm-9">
        <h4><?= Yii::t('podium/view', 'Subscriptions') ?></h4>
        <?= Html::beginForm(); ?>
<?php Pjax::begin(); ?>
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'filterSelector' => 'select#per-page',
    'tableOptions'   => ['class' => 'table table-striped table-hover'],
    'columns'        => [
        [
            'class'           => CheckboxColumn::className(),
            'headerOptions'   => ['class' => 'col-sm-1 text-center'],
            'contentOptions'  => ['class' => 'col-sm-1 text-center'],
            'checkboxOptions' => function($model) {
                return ['value' => $model->id];
            }
        ],
        [
            'attribute' => 'thread.name',
            'label'     => Yii::t('podium/view', "Thread's Name"),
            'format'    => 'raw',
            'value'     => function ($model) {
                return Html::a($model->thread->name, ['default/show', 'id' => $model->thread->latest->id], ['class' => 'center-block']);
            },
        ],
        [
            'attribute'      => 'post_seen',
            'headerOptions'  => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'label'          => Yii::t('podium/view', 'New Posts'),
            'format'         => 'raw',
            'value'          => function ($model) {
                return $model->post_seen ? '' : '<span class="glyphicon glyphicon-ok-sign"></span>';
            },
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{mark} {delete}',
            'buttons'        => [
                'mark' => function($url, $model) {
                    if ($model->post_seen) {
                        return Html::a('<span class="glyphicon glyphicon-eye-close"></span> ' . Yii::t('podium/view', 'Mark unseen'), $url, ['class' => 'btn btn-warning btn-xs']);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Mark seen'), $url, ['class' => 'btn btn-success btn-xs']);
                    }
                },
                'delete' => function($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('podium/view', 'Unsubscribe'), $url, ['class' => 'btn btn-danger btn-xs']);
                },
            ],
        ]
    ],
]); ?>
<?php Pjax::end(); ?>
            <div class="row">
                <div class="col-sm-12">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-trash"></span> ' . Yii::t('podium/view', 'Unsubscribe Selected Threads'), ['class' => 'btn btn-danger btn-sm', 'name' => 'delete-button']) ?>
                </div>
            </div>
        <?= Html::endForm(); ?>
    </div>
</div><br>
