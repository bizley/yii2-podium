<?php

use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\ArrayHelper;
use bizley\podium\models\User;

$this->title                   = Yii::t('podium/view', 'Forum Members');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'),
    'url'   => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

echo $this->render('/elements/admin/_navbar', ['active' => 'members']);
?>

<br>

<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns'      => [
        [
            'attribute'          => 'id',
            'label'              => Yii::t('podium/view', 'ID'),
            'contentOptions'     => ['class' => 'col-sm-1 text-right'],
            'headerOptions'      => ['class' => 'col-sm-1 text-right'],
        ],
        [
            'attribute'          => 'username',
            'label'              => Yii::t('podium/view', 'Username'),
        ],
        [
            'attribute'          => 'email',
            'label'              => Yii::t('podium/view', 'E-mail'),
        ],
        [
            'label'              => Yii::t('podium/view', 'Role'),
            'value'              => function ($model) {
                $roles = Yii::$app->authManager->getRolesByUser($model->id);
                return ucfirst(reset($roles)->name);
            },
        ],
        [
            'attribute'          => 'status',
            'label'              => Yii::t('podium/view', 'Status'),
            'filter'             => User::getStatuses(),
            'value'              => function ($model) {
                return ArrayHelper::getValue(User::getStatuses(), $model->status);
            },
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{view} {update} {ban} {delete}',
            'buttons'        => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Member')]);
                },
                'update' => function($url, $model) {
                    if ($model->id !== Yii::$app->user->id) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['class' => 'btn btn-primary btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Update Member')]);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['class' => 'btn btn-xs disabled text-muted']);
                    }
                },
                'ban'            => function($url, $model) {
                    if ($model->id !== Yii::$app->user->id) {
                        if ($model->status !== User::STATUS_BANNED) {
                            return Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', $url, ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Ban Member')]);
                        }
                        else {
                            return Html::a('<span class="glyphicon glyphicon-ok-circle"></span>', '#', ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Unban Member')]);
                        }
                    }
                    return Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                },
                'delete' => function($url, $model) {
                    if ($model->id !== Yii::$app->user->id) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, ['class' => 'btn btn-primary btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Member')]);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                    }
                },
            ],
        ]
    ],
]);
