<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\quill\Quill;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$this->title = Yii::t('podium/view', 'New Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");

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
                <div class="col-sm-9">
<?php if (empty($to)): ?>                   
                    <?= $form->field($model, 'receiversId[]')->widget(Select2::classname(), [
                            'options'       => ['placeholder' => Yii::t('podium/view', 'Select a member...')],
                            'theme'         => Select2::THEME_KRAJEE,
                            'pluginOptions' => [
                                'allowClear'         => true,
                                'multiple'           => true,
                                'minimumInputLength' => 3,
                                'ajax'               => [
                                    'url'      => Url::to(['members/fieldlist']),
                                    'dataType' => 'json',
                                    'data'     => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            ],
                        ])->label(false); ?>
<?php else: ?>                    
                    <p class="form-control-static"><?= $to->getPodiumTag(true) ?></p>
                    <?= $form->field($model, 'receiversId[]')->hiddenInput(['value' => $model->receiversId[0]])->label(false) ?>
<?php endif; ?>
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
                    <?= $form->field($model, 'content')->label(false)->widget(Quill::className(), ['options' => ['style' => 'height:320px']]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-9 col-sm-offset-3">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Send Message'), ['class' => 'btn btn-block btn-primary', 'name' => 'send-button']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div><br>
