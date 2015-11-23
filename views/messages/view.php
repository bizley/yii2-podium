<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\Message;
use bizley\podium\widgets\Avatar;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'View Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip()");

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
        <div <?= Helper::replyBgd() ?>>
            <div class="row">
                <div class="col-sm-3 text-center">
                    <?= Avatar::widget(['author' => $model->senderUser]) ?>
                </div>
                <div class="col-sm-9">
                    <div class="popover right podium">
                        <div class="arrow"></div>
                        <div class="popover-title">
                            <small class="pull-right"><span data-toggle="tooltip" data-placement="top" title="<?= Yii::$app->formatter->asDatetime($model->created_at, 'long') ?>"><?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></span></small>
                            <?= Html::encode($model->topic) ?>
                        </div>
                        <div class="popover-content">
                            <?= $model->content ?>
                            <div class="text-right">
<?php if ($model->sender_id != Yii::$app->user->id && $model->senderUser !== null): ?>
                                <a href="<?= Url::to(['messages/reply', 'id' => $model->id]) ?>" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="bottom" title="<?= Yii::t('podium/view', 'Reply to Message') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
<?php else: ?>
                                <a href="#" class="btn btn-xs disabled text-muted"><span class="glyphicon glyphicon-share-alt"></span></a>
<?php endif; ?>
                                <span data-toggle="modal" data-target="#podiumModal"><button class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="bottom" title="<?= Yii::t('podium/view', 'Delete Message') ?>"><span class="glyphicon glyphicon-trash"></span></button></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
<?php $stack = 0; $reply = clone $model; while ($reply->reply && $stack < 5): ?>
<?php if (($reply->reply->receiver_id == Yii::$app->user->id && $reply->reply->receiver_status == Message::STATUS_REMOVED) || 
        ($reply->reply->sender_id == Yii::$app->user->id && $reply->reply->sender_status == Message::STATUS_REMOVED)): ?>
<?php $reply = $reply->reply; else: ?>
            <div class="row">
                <div class="col-sm-2 text-center">
                    <?= Avatar::widget(['author' => $reply->reply->senderUser]) ?>
                </div>
                <div class="col-sm-10">
                    <div class="popover right podium">
                        <div class="arrow"></div>
                        <div class="popover-title">
                            <small class="pull-right"><span data-toggle="tooltip" data-placement="top" title="<?= Yii::$app->formatter->asDatetime($reply->reply->created_at, 'long') ?>"><?= Yii::$app->formatter->asRelativeTime($reply->reply->created_at) ?></span></small>
                            <?= Html::encode($reply->reply->topic) ?>
                        </div>
                        <div class="popover-content">
                            <?= $reply->reply->content ?>
                        </div>
                    </div>
                </div>
            </div>
<?php $reply = $reply->reply; $stack++; endif; endwhile; ?>
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
