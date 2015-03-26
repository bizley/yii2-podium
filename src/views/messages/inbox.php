<?php

use yii\helpers\Html;
use yii\grid\GridView;
use bizley\podium\components\Helper;
use yii\web\View;
use yii\grid\ActionColumn;

$this->title                   = Yii::t('podium/view', 'Messages Inbox');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'inbox']) ?>
        
        <br>
        
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns'      => [
        [
            'attribute'          => 'sender.username',
            'label'              => Yii::t('podium/view', 'From') . Helper::sortOrder('sender'),
            'encodeLabel'        => false,
        ],
        [
            'attribute'          => 'topic',
            'label'              => Yii::t('podium/view', 'Topic') . Helper::sortOrder('topic'),
            'encodeLabel'        => false,
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{view} {reply} {delete}',
            'buttons'        => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Message')]);
                },
                'reply' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-share-alt"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Reply to Message')]);
                },
                'delete' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Message')]);
                },
            ],
        ]
    ],
]); ?>
    </div>
</div><br>