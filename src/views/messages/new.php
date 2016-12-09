<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\widgets\editor\EditorBasic;
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
    <div class="col-md-3 col-sm-4">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-md-9 col-sm-8">
        <?= $this->render('/elements/messages/_navbar', ['active' => 'new']) ?>
        <br>
        <?php $form = ActiveForm::begin(['id' => 'message-form']); ?>
            <div class="row">
                <div class="col-md-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Send to') ?></p></div>
<?php if (!empty($to)): ?>
                <div class="col-md-9">
                    <p class="form-control-static"><?= $to->getPodiumTag(true) ?></p>
                    <?= $form->field($model, 'receiversId[]')->hiddenInput(['value' => $model->receiversId[0]])->label(false) ?>
                </div>
<?php else: ?>
<?php if (!empty($friends)): ?>
                <div class="col-md-4">
                    <?= $form->field($model, 'friendsId[]')->widget(Select2::classname(), [
                            'options'       => ['placeholder' => Yii::t('podium/view', 'Select a friend...')],
                            'theme'         => Select2::THEME_KRAJEE,
                            'showToggleAll' => false,
                            'data'          => $friends,
                            'pluginOptions' => [
                                'allowClear'   => true,
                                'multiple'     => true,
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            ],
                        ])->label(false); ?>
                </div>
                <div class="col-md-1"><p class="form-control-static"><?= Yii::t('podium/view', 'and/or') ?></p></div>
                <div class="col-md-4">
<?php else: ?>
                <div class="col-md-9">
<?php endif; ?>
                    <?= $form->field($model, 'receiversId[]')->widget(Select2::classname(), [
                            'options'       => ['placeholder' => Yii::t('podium/view', 'Select a member...')],
                            'theme'         => Select2::THEME_KRAJEE,
                            'showToggleAll' => false,
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
                </div>
<?php endif; ?>
            </div>
            <div class="row">
                <div class="col-md-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Topic') ?></p></div>
                <div class="col-md-9">
                    <?= $form->field($model, 'topic')->textInput(['placeholder' => Yii::t('podium/view', 'Message Topic')])->label(false) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Message Content') ?></p></div>
                <div class="col-md-9">
                    <?= $form->field($model, 'content')->label(false)->widget(EditorBasic::className()) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 col-md-offset-3">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Send Message'), ['class' => 'btn btn-block btn-primary', 'name' => 'send-button']) ?>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div><br>
