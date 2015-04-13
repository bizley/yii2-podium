<?php

use bizley\podium\components\Helper;
use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

$this->title                   = Yii::t('podium/view', 'Sent Messages');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip();', View::POS_READY, 'bootstrap-tooltip');
$this->registerJs('jQuery(\'#podiumModal\').on(\'show.bs.modal\', function(e) {
    var button = jQuery(e.relatedTarget);
    var url = button.data(\'url\');
    jQuery(\'#deleteUrl\').attr(\'href\', url);
});', View::POS_READY, 'bootstrap-modal-delete');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'sent']) ?>
        
        <br>
        
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'filterSelector' => 'select#per-page',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns'      => [
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
            'template'       => '{view} {delete}',
            'buttons'        => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Message')]);
                },
                'delete' => function($url) {
                    return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Message')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModal', 'data-url' => $url]);
                },
            ],
        ]
    ],
]); ?>
    </div>
</div><br>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModal" aria-hidden="true" id="podiumModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= Yii::t('podium/view', 'Delete message') ?></h4>
            </div>
            <div class="modal-body">
                <?= Yii::t('podium/view', 'Are you sure you want to move this message to Deleted Messages?') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete message') ?></a>
            </div>
        </div>
    </div>
</div>