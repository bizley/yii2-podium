<?php

use bizley\ajaxdropdown\AjaxDropdown;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Yii::t('podium/view', 'New Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('$(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

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
                <div class="col-sm-8">
                    <?= $form->field($model, 'receiver_id')->widget(AjaxDropdown::classname(), [
                        'source' => Url::to(['members/fieldlist']),
                        'singleMode' => true,
                        'inputOptions' => ['placeholder' => Yii::t('podium/view', 'Select a member...')]
                        ])->label(false); ?>
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
                                'toolbar' => [
                                    ['style', ['bold', 'italic', 'underline']],
                                    ['para', ['ul', 'ol']],
                                    ['insert', ['link', 'picture']],
                                ],
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
    </div>
</div><br>