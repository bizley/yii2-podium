<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\bootstrap\Progress;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'New Installation');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Podium Installation'), 'url' => ['install/run']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['no-search']     = true;

$this->registerJs("var nextStep = function(step) { $.ajax({url: '" . Url::to(['install/import']) . "', method: 'POST', data: {step: step}, dataType: 'json'}).fail(function(){ $('#progressBar').addClass('hide'); $('#installationError').removeClass('hide'); }).done(function(data){ $('#progressBar .progress-bar').css('width', data.percent + '%').attr('aria-valuenow', data.percent).html(data.percent + '%'); $('#installationProgress .list-group').prepend('<li class=\"list-group-item\"><strong>' + data.table + '</strong> ' + data.result + '</li>'); if (data.percent < 100) nextStep(++step); else { $('#progressBar .progress-bar').removeClass('active progress-bar-striped'); if (data.error) $('#installationFinishedError').removeClass('hide'); else $('#installationFinished').removeClass('hide'); }}); }; $('#installPodium').click(function(e){ e.preventDefault(); $('#startInstallation').addClass('hide'); $('#installationResults').removeClass('hide'); $('#progressBar .progress-bar').css('width', '10px'); var firstStep = $('#drop')[0].checked == true ? -1 : 0; nextStep(firstStep); });");
?>
<div class="row" id="startInstallation">
    <div class="text-center col-sm-12">
        <em><?= Yii::t('podium/view', 'Podium will attempt to create all database tables required by the forum along with the default configuration and the administrator account.') ?></em><br>
        <em><strong><?= Yii::t('podium/view', 'Back up your existing database and then click the button below.') ?></strong></em><br><br>
        <div class="alert alert-danger">
            <label><input type="checkbox" name="drop" value="1" id="drop"> <?= Yii::t('podium/view', 'Check this box to drop all existing Podium tables first') ?> <span class="glyphicon glyphicon-alert"></span></label><br>
            <?= Yii::t('podium/view', '(all existing Podium data will be deleted)') ?>
        </div>
        <button id="installPodium" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-import"></span> <?= Yii::t('podium/view', 'Start Podium Installation') ?></button><br><br>
        <?= Yii::t('podium/view', 'Version to install') ?> <kbd><?= $version ?></kbd>
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
        <div class="alert alert-danger" role="alert"><?= Yii::t('podium/view', 'There was a major error during installation! Check your runtime log for details.') ?></div>
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
