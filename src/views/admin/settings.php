<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title                   = Yii::t('podium/view', 'Podium Settings');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo  $this->render('/elements/admin/_navbar', ['active' => 'settings']); ?>

<br>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <div class="panel panel-default">
<?php $form = ActiveForm::begin(['id' => 'settings-form']); ?>
            <div class="panel-heading">
                <h3 class="panel-title"><?= Yii::t('podium/view', 'Podium Settings') ?></h3>
            </div>
            <div class="panel-body">
                <p><?= Yii::t('podium/view', 'Leave setting empty if you want to restore the default Podium value.') ?></p>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'name')->textInput()->label(Yii::t('podium/view', 'Forum\'s Name')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'hot_minimum')->textInput()->label(Yii::t('podium/view', 'Minimum number of posts for thread to become Hot')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'members_visible')->checkBox()->label(Yii::t('podium/view', 'Allow guests to list members')) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($model, 'version')->textInput(['readonly' => true])->label(Yii::t('podium/view', 'Database version')) ?>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-12">
                        <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save Settings'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                    </div>
                </div>
            </div>
<?php ActiveForm::end(); ?>
        </div>
    </div>
</div><br>