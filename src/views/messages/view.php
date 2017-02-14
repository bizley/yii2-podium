<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\helpers\Helper;
use bizley\podium\models\Message;
use bizley\podium\models\User;
use bizley\podium\Podium;
use bizley\podium\widgets\Avatar;
use bizley\podium\widgets\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'View Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
if ($type == 'received') {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Messages Inbox'), 'url' => ['messages/inbox']];
}
else {
    $this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Sent Messages'), 'url' => ['messages/sent']];
}
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");

$loadOlder    = '<span class="glyphicon glyphicon-import"></span> ' . Yii::t('podium/view', 'Load older messages');
$loadingOlder = '<span class="glyphicon glyphicon-hourglass"></span> ' . Yii::t('podium/view', 'Loading messages...');
$lastOne      = '<span class="glyphicon glyphicon-stop"></span> ' . Yii::t('podium/view', 'Last message in the thread');
$this->registerJs("var loading = false; $('.load-messages').click(function (e) { e.preventDefault(); if (loading === false) { loading = true; $('.load-messages').html('$loadingOlder').addClass('disabled'); $.post('" . Url::to(['messages/load']) . "', {message:$(this).data('last')}, null, 'json').fail(function(){ console.log('Message Load Error!'); }).done(function(data){ $('#loadedMessages').append(data.messages); if (parseInt(data.more) > 0) { $('.load-messages').html('$loadOlder').removeClass('disabled').data('last', data.more); } else { $('.load-messages').html('$lastOne') } }).always(function(){ loading = false; }); } });");

$loggedId = User::loggedId();

?>
<div class="row">
    <div class="col-md-3 col-sm-4">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-md-9 col-sm-8">
        <?= $this->render('/elements/messages/_navbar', ['active' => 'view']) ?>
        <br>
        <div <?= Helper::replyBgd() ?>>
            <div class="row">
                <div class="col-sm-3 text-center">
                    <?= Avatar::widget(['author' => $model->sender]) ?>
                </div>
                <div class="col-sm-9">
                    <div class="popover right podium">
                        <div class="arrow"></div>
                        <div class="popover-title">
                            <small class="pull-right"><span data-toggle="tooltip" data-placement="top" title="<?= Podium::getInstance()->formatter->asDatetime($model->created_at, 'long') ?>"><?= Podium::getInstance()->formatter->asRelativeTime($model->created_at) ?></span></small>
                            <?= Html::encode($model->topic) ?>
                        </div>
                        <div class="popover-content">
                            <?= $model->parsedContent ?>
                            <div class="text-right">
<?php if ($type == 'received'): ?>
                                <a href="<?= Url::to(['messages/reply', 'id' => $model->id]) ?>" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="bottom" title="<?= Yii::t('podium/view', 'Reply to Message') ?>"><span class="glyphicon glyphicon-share-alt"></span></a>
<?php endif; ?>
                                <span data-toggle="modal" data-target="#podiumModal"><button class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="bottom" title="<?= Yii::t('podium/view', 'Delete Message') ?>"><span class="glyphicon glyphicon-trash"></span></button></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php $stack = 0; $reply = clone $model; while ($reply->reply && $stack < 5): $more = 0; ?>
<?php if ($reply->reply->sender_id == $loggedId && $reply->reply->sender_status == Message::STATUS_DELETED) { $reply = $reply->reply; continue; } ?>
            <?= $this->render('load', ['reply' => $reply]) ?>
<?php $reply = $reply->reply; if ($reply) { $more = $reply->id; } $stack++; endwhile; ?>

            <div id="loadedMessages"></div>

<?php if (!empty($more)): ?>
            <div class="row">
                <div class="col-sm-12 text-right"><a href="#" data-last="<?= $more ?>" class="load-messages btn btn-default"><?= $loadOlder ?></a></div>
            </div>
<?php endif; ?>

        </div>
    </div>
</div><br>

<?php Modal::begin([
    'id' => 'podiumModal',
    'header' => Yii::t('podium/view', 'Delete Message'),
    'footer' => Yii::t('podium/view', 'Delete Message'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger'],
    'footerConfirmUrl' => Url::to(['messages/delete-' . $type, 'id' => $id])
 ]) ?>
<?= Yii::t('podium/view', 'Are you sure you want to delete this message?') ?>
<?php Modal::end();
