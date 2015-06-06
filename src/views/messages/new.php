<?php
use bizley\ajaxdropdown\AjaxDropdown;
use bizley\podium\components\Helper;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use Zelenin\yii\widgets\Summernote\Summernote;

$this->title                   = Yii::t('podium/view', 'New Message');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'My Profile'), 'url' => ['profile/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('jQuery(\'[data-toggle="tooltip"]\').tooltip()', View::POS_READY, 'bootstrap-tooltip');

$this->registerJs('jQuery(\'#test\').click(function(){ jQuery(\'#message-receiver_id_ajaxDropDownWidget\').trigger(\'add\', [{id:1,value:"test",mark:0,additional:"<b>aaa</b>"}, {id:2,value:"dwa",mark:1}]); })', View::POS_READY, 'test-click');
$this->registerJs('jQuery(\'#test2\').click(function(){ jQuery(\'#message-receiver_id_ajaxDropDownWidget\').trigger(\'removeOne\', [1,2]); })', View::POS_READY, 'test-click2');
$this->registerJs('jQuery(\'#test3\').click(function(){ jQuery(\'#message-receiver_id_ajaxDropDownWidget\').trigger(\'removeAll\'); })', View::POS_READY, 'test-click3');

?>
<div class="row">
    <div class="col-sm-3">
        <?= $this->render('/elements/profile/_navbar', ['active' => 'messages']) ?>
    </div>
    <div class="col-sm-9">
        
        <?= $this->render('/elements/messages/_navbar', ['active' => 'new']) ?>
        
        <br>
        
        <button class="btn btn-danger" id="test">add</button>
        <button class="btn btn-danger" id="test2">remove 1</button>
        <button class="btn btn-danger" id="test3">remove all</button>
        
        <?php $form = ActiveForm::begin(['id' => 'message-form']); ?>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Send to') ?></p></div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'receiver_id')->widget(AjaxDropdown::classname(), [
                        'source' => Url::to(['members/fieldlist2']),
                        //'singleMode' => true,
                        //'singleModeBottom' => true,
                        //'additionalCode' => '---',
                        'inputOptions' => ['placeholder' => Yii::t('podium/view', 'Select a member...')],
                        'data' => $data
                        ])->label(false); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-right"><p class="form-control-static"><?= Yii::t('podium/view', 'Send to') ?></p></div>
                <div class="col-sm-9">
                    <?= $form->field($model, 'receiver_id')->widget(Select2::classname(), [
                            'options'       => ['placeholder' => Yii::t('podium/view', 'Select a member...')],
                            'theme'         => Select2::THEME_KRAJEE,
                            'pluginOptions' => [
                                'allowClear'         => true,
                                'minimumInputLength' => 3,
                                'ajax'               => [
                                    'url'      => Url::to(['members/fieldlist']),
                                    'dataType' => 'json',
                                    'data'     => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup'       => new JsExpression('function (markup) { return markup; }'),
                            ],
                        ])->label(false); ?>
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
    </div>
</div><br>