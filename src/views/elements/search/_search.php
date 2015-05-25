<?php

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
?>
<div class="col-sm-10 col-sm-offset-1">
    <?php $form = ActiveForm::begin(['id' => 'search-form']); ?>
        <div class="row">
            <div class="form-group">
                <?= $form->field($model, 'query')->textInput(['class' => 'form-control input-lg'])->label(Yii::t('podium/view', 'Find words')) ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <div class="col-sm-3">
                    <?= $form->field($model, 'match')->radioList(['all' => Yii::t('podium/view', 'all words'), 'any' => Yii::t('podium/view', 'any word')], ['unselect' => 'all', 'itemOptions' => ['class' => 'test']])->label(Yii::t('podium/view', 'Match')) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($model, 'author')->textInput()->label(Yii::t('podium/view', 'Author')) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($model, 'date_from')->widget(DatePicker::classname(), ['removeButton' => false, 'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy/mm/dd']])->label(Yii::t('podium/view', 'Date from')) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($model, 'date_to')->widget(DatePicker::classname(), ['removeButton' => false, 'pluginOptions' => ['autoclose' => true, 'format' => 'yyyy/mm/dd']])->label(Yii::t('podium/view', 'Date to')) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <?= $form->field($model, 'forums')->dropDownList($list, ['multiple' => true, 'encode' => false])->label(Yii::t('podium/view', 'Search in Forums')) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>