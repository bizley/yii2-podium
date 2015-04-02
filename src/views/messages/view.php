<?php

use bizley\podium\models\Message;
use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title                   = Yii::t('podium/view', 'View Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

$perm = false;
if (($model->receiver_id == Yii::$app->user->id && $model->receiver_status == Message::STATUS_DELETED) || 
        ($model->sender_id == Yii::$app->user->id && $model->sender_status == Message::STATUS_DELETED)) {
    $perm = true;
}

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'view']) ?>
        
        <br>
        
        <div class="row">
            <div class="col-sm-3 text-center">
                <?= Avatar::widget(['author' => $model->senderUser]) ?>
            </div>
            <div class="col-sm-9">
                <div class="popover right podium">
                    <div class="arrow"></div>
                    <div class="popover-title">
                        <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($model->created_at, 'long')]); ?></small>
                        <?= Html::encode($model->topic) ?>
                    </div>
                    <div class="popover-content">
                        <?= $model->content ?>
                        <div class="text-right">
<?php if ($model->sender_id != Yii::$app->user->id && $model->senderUser !== null): ?>
                            <?= Html::a('<span class="glyphicon glyphicon-share-alt"></span>', ['reply', 'id' => $model->id], ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => Yii::t('podium/view', 'Reply to Message')]) ?>
<?php else: ?>
                            <?= Html::a('<span class="glyphicon glyphicon-share-alt"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']); ?>
<?php endif; ?>
                            <?= Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom', 'title' => Yii::t('podium/view', 'Delete Message')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModal']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div><br>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModal" aria-hidden="true" id="podiumModal">
<?php if ($perm): ?>
    <div class="modal-dialog">
<?php else: ?>
    <div class="modal-dialog modal-sm">
<?php endif; ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
<?php if ($perm): ?>
                    <?= Yii::t('podium/view', 'Delete message permanently') ?>
<?php else: ?>
                    <?= Yii::t('podium/view', 'Delete message') ?>
<?php endif; ?>
                </h4>
            </div>
            <div class="modal-body">
<?php if ($perm): ?>
                <?= Yii::t('podium/view', 'Are you sure you want to delete this message permanently?') ?>
<?php else: ?>
                <?= Yii::t('podium/view', 'Are you sure you want to move this message to Deleted Messages?') ?>
<?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
<?php if ($perm): ?>
                <a href="<?= Url::to(['delete', 'id' => $model->id, 'perm' => 1]) ?>" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete message permanently') ?></a>
<?php else: ?>
                <a href="<?= Url::to(['delete', 'id' => $model->id]) ?>" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete message') ?></a>
<?php endif; ?>
            </div>
        </div>
    </div>
</div>