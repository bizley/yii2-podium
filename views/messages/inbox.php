<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\Message;
use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Messages Inbox');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
$this->registerJs("$('#podiumModal').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); var url = button.data('url'); $('#deleteUrl').attr('href', url); });");

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        <?= $this->render('/elements/messages/_navbar', ['active' => 'inbox']) ?>
        <br>
<?php Pjax::begin(); ?>
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'filterModel'    => $searchModel,
    'filterSelector' => 'select#per-page',
    'tableOptions'   => ['class' => 'table table-striped table-hover'],
    'rowOptions'     => function($model) {
        return $model->receiver_status == Message::STATUS_NEW ? ['class' => 'warning'] : null;
    },
    'columns' => [
        [
            'attribute'   => 'senderName',
            'label'       => Yii::t('podium/view', 'From') . Helper::sortOrder('senderName'),
            'encodeLabel' => false,
            'format'      => 'raw',
            'value'       => function($model) {
                return $model->message->sender->podiumTag;
            }
        ],
        [
            'attribute'   => 'topic',
            'label'       => Yii::t('podium/view', 'Topic') . Helper::sortOrder('topic'),
            'encodeLabel' => false,
            'format'      => 'raw',
            'value'       => function($model) {
                return Html::a(Html::encode($model->message->topic), ['messages/view-received', 'id' => $model->id], ['data-pjax' => '0']);
            }
        ],
        [
            'attribute'   => 'created_at',
            'label'       => Yii::t('podium/view', 'Sent') . Helper::sortOrder('created_at'),
            'encodeLabel' => false,
            'format'      => 'raw',
            'value'       => function($model) {
                return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->message->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->message->created_at, 'long')]);
            }
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{view-received} {reply} {delete-received}',
            'buttons'        => [
                'view-received' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-pjax' => '0', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Message')]);
                },
                'reply' => function($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-share-alt"></span>', $url, ['class' => 'btn btn-success btn-xs', 'data-pjax' => '0', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Reply to Message')]);
                },
                'delete-received' => function($url) {
                    return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Message')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModal', 'data-url' => $url]);
                },
            ],
        ]
    ],
]); ?>
<?php Pjax::end(); ?>
    </div>
</div><br>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalDeleteLabel" aria-hidden="true" id="podiumModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalDeleteLabel"><?= Yii::t('podium/view', 'Delete message') ?></h4>
            </div>
            <div class="modal-body">
                <?= Yii::t('podium/view', 'Are you sure you want to delete this message?') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete message') ?></a>
            </div>
        </div>
    </div>
</div>
