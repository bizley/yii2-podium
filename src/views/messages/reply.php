<?php

use bizley\podium\components\Helper;
use bizley\podium\models\Message;
use bizley\podium\widgets\Avatar;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Yii::t('podium/view', 'Reply to Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'new']) ?>
        
        <br>
        
        <?php $form = ActiveForm::begin(['id' => 'message-form']); ?>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Send to') ?></p></div>
                <div class="col-sm-8"><p class="form-control-static"><?= $reply->senderUser->getPodiumTag(true) ?></p>
                    <?= $form->field($model, 'receiver_id')->hiddenInput()->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Topic') ?></p></div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'topic')->textInput(['placeholder' => Yii::t('podium/view', 'Message Topic')])->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Content') ?></p></div>
                <div class="col-sm-8">
                    <?= $form->field($model, 'content')->label(false)->widget(Summernote::className(), [
                            'clientOptions' => [
                                'height' => '100',
                                'lang' => Yii::$app->language != 'en-US' ? Yii::$app->language : null,
                                'codemirror' => null,
                                'toolbar' => Helper::summerNoteToolbars('full'),
                            ],
                        ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-8 col-sm-offset-3">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Send Message'), ['class' => 'btn btn-block btn-primary', 'name' => 'send-button']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
        <br>
        <div <?= Helper::replyBgd() ?>>
            <div class="row">
                <div class="col-sm-2 text-center">
                    <?= Avatar::widget(['author' => $reply->senderUser]) ?>
                </div>
                <div class="col-sm-10">
                    <div class="popover right podium">
                        <div class="arrow"></div>
                        <div class="popover-title">
                            <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($reply->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($reply->created_at, 'long')]); ?></small>
                            <?= Html::encode($reply->topic) ?>
                        </div>
                        <div class="popover-content">
                            <?= $reply->content ?>
                        </div>
                    </div>
                </div>
            </div>

<?php $stack = 0; while ($reply->reply && $stack < 4): ?>
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
                            <small class="pull-right"><?= Html::tag('span', Yii::$app->formatter->asRelativeTime($reply->reply->created_at), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::$app->formatter->asDatetime($reply->reply->created_at, 'long')]); ?></small>
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