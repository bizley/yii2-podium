<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\Message;
use bizley\podium\models\User;
use bizley\podium\widgets\Avatar;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title = Yii::t('podium/view', 'Reply to Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");

$loggedId = User::loggedId();

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
                <div class="col-sm-9"><p class="form-control-static"><?= $reply->sender->getPodiumTag(true) ?></p>
                    <?= $form->field($model, 'receiversId[]')->hiddenInput(['value' => $model->receiversId[0]])->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Topic') ?></p></div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'topic')->textInput(['placeholder' => Yii::t('podium/view', 'Message Topic')])->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Content') ?></p></div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'content')->label(false)->widget(Summernote::className(), [
                            'clientOptions' => [
                                'height'     => '100',
                                'lang'       => Yii::$app->language != 'en-US' ? Yii::$app->language : null,
                                'codemirror' => null,
                                'toolbar'    => Helper::summerNoteToolbars('full'),
                            ],
                        ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-9 col-sm-offset-3">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Send Message'), ['class' => 'btn btn-block btn-primary', 'name' => 'send-button']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
        <br>
        <div <?= Helper::replyBgd() ?>>
            <div class="row">
                <div class="col-sm-2 text-center">
                    <?= Avatar::widget(['author' => $reply->sender]) ?>
                </div>
                <div class="col-sm-10">
                    <div class="popover right podium">
                        <div class="arrow"></div>
                        <div class="popover-title">
                            <small class="pull-right"><span data-toggle="tooltip" data-placement="top" title="<?= Yii::$app->formatter->asDatetime($reply->created_at, 'long') ?>"><?= Yii::$app->formatter->asRelativeTime($reply->created_at) ?></span></small>
                            <?= Html::encode($reply->topic) ?>
                        </div>
                        <div class="popover-content">
                            <?= $reply->content ?>
                        </div>
                    </div>
                </div>
            </div>

<?php $stack = 0; while ($reply->reply && $stack < 4): ?>
<?php if ($reply->reply->sender_id == $loggedId && $reply->reply->sender_status == Message::STATUS_DELETED) { $reply = $reply->reply; continue; } ?>

            <div class="row">
                <div class="col-sm-2 text-center">
                    <?= Avatar::widget(['author' => $reply->reply->sender]) ?>
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
<?php $reply = $reply->reply; $stack++; endwhile; ?>
        </div>

    </div>
</div><br>
