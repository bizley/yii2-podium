<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Progress;

$js = "var nextStep = function(step, version){
    jQuery.ajax({
        url: '" . Url::to(['install/update']) . "',
        method: 'POST',
        data: {step: step, from: version},
        dataType: 'json'
    }).fail(function(){
        jQuery('#progressBar').addClass('hide');
        jQuery('#installationError').removeClass('hide');
    }).done(function(data){
        jQuery('#progressBar .progress-bar').css('width', data.percent+'%').attr('aria-valuenow', data.percent).html(data.percent+'%');
        jQuery('#installationProgress .list-group').prepend('<li class=\"list-group-item\"><strong>'+data.table+'</strong> '+data.result+'</li>');
        if (data.percent < 100) nextStep(++step, version);
        else {
            jQuery('#progressBar .progress-bar').removeClass('active progress-bar-striped');
            if (data.error) jQuery('#installationFinishedError').removeClass('hide');
            else jQuery('#installationFinished').removeClass('hide');
        }
    });
};
jQuery('#installPodium').click(function(e){
    e.preventDefault();
    jQuery('#startInstallation').addClass('hide');
    jQuery('#installationResults').removeClass('hide');
    jQuery('#progressBar .progress-bar').css('width', '10px');
    nextStep(0, '$dbVersion');
});";

$this->registerJs($js, View::POS_READY, 'podium-update');

$this->title                   = Yii::t('podium/view', 'Podium Upgrade');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Podium Installation'), 'url' => ['install/run']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['no-search']     = true;
?>
<div class="row" id="startInstallation">
    <div class="text-center col-sm-12">
<?php if ($error == '' && $info == ''): ?>
        <em><?= Yii::t('podium/view', 'Podium will attempt to update all database tables to the current version.') ?></em><br>
        <em><?= Yii::t('podium/view', '<strong>Back up your existing database</strong> and then click the button below.') ?></em><br><br>
        <button id="installPodium" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-open"></span> <?= Yii::t('podium/view', 'Upgrade Podium Database') ?></button><br><br>
<?php elseif ($error != ''): ?>
        <div class="alert alert-danger"><?= $error ?></div>
<?php elseif ($info != ''): ?>
        <div class="alert alert-success"><?= $info ?></div>
<?php endif; ?>
        <?= Yii::t('podium/view', 'Current database version') ?> <kbd><?= $dbVersion ?></kbd> <span class="glyphicon glyphicon-transfer"></span> <kbd><?= $currentVersion ?></kbd> <?= Yii::t('podium/view', 'Current module version') ?>
    </div>
</div>
<div class="row hide" id="installationResults">
    <div class="text-center col-sm-8 col-sm-offset-2" id="progressBar">
        <?= Progress::widget([
            'percent'    => 0,
            'label'      => '0%',
            'barOptions' => ['class' => 'progress-bar progress-bar-striped active'],
            'options'    => ['class' => 'progress']
        ]) ?>      
    </div>
    <div class="col-sm-8 col-sm-offset-2 hide" id="installationError">
        <div class="alert alert-danger" role="alert"><?= Yii::t('podium/view', 'There was a major error during installation...') ?></div>
    </div>
    <div class="row hide" id="installationFinished">
        <div class="text-center col-sm-12">
            <a href="<?= Url::to(['default/index']) ?>" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-ok-sign"></span> <?= Yii::t('podium/view', 'Installation finished') ?></a>
        </div>
    </div>
    <div class="row hide" id="installationFinishedError">
        <div class="text-center col-sm-12">
            <button class="btn btn-danger btn-lg"><span class="glyphicon glyphicon-alert"></span> <?= Yii::t('podium/view', 'Errors during installation') ?></button>
        </div>
    </div><br>
    <div class="col-sm-8 col-sm-offset-2" id="installationProgress">
        <ul class="list-group"></ul>
    </div>
</div>