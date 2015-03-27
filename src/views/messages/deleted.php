<?php

use yii\helpers\Html;
use yii\grid\GridView;
use bizley\podium\components\Helper;
use bizley\podium\models\Message;
use yii\web\View;
use yii\grid\ActionColumn;

$this->title                   = Yii::t('podium/view', 'Deleted Messages');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'trash']) ?>
        
        <br>
        
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns'      => [
        [
            'attribute'          => 'senderName',
            'label'              => Yii::t('podium/view', 'From') . Helper::sortOrder('senderName'),
            'encodeLabel'        => false,
            'format'             => 'raw',
            'value'              => function($model) {
                return $model->getSenderName();
            }
        ],
        [
            'attribute'          => 'receiverName',
            'label'              => Yii::t('podium/view', 'To') . Helper::sortOrder('receiverName'),
            'encodeLabel'        => false,
            'format'             => 'raw',
            'value'              => function($model) {
                return $model->getReceiverName();
            }
        ],
        [
            'attribute'          => 'topic',
            'label'              => Yii::t('podium/view', 'Topic') . Helper::sortOrder('topic'),
            'encodeLabel'        => false,
            'format'             => 'raw',
            'value'              => function($model) {
                return Html::a(Html::encode($model->topic), ['view', 'id' => $model->id]);
            }
        ],
        [
            'attribute'          => 'created_at',
            'label'              => Yii::t('podium/view', 'Sent') . Helper::sortOrder('created_at'),
            'encodeLabel'        => false,
            'format'             => 'raw',
            'value'              => function($model) {
                return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]);
            }
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
                    return Html::a('<span class="glyphicon glyphicon-share-alt"></span>', $url, ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Reply to Message')]);
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