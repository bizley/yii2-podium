<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\bootstrap\Progress;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Podium update');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Podium Installation'), 'url' => ['install/run']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['no-search']     = true;

$this->registerJs("var nextStep = function(step, version) { $.ajax({url: '" . Url::to(['install/update']) . "', method: 'POST', data: {step: step, from: version}, dataType: 'json'}).fail(function(){ $('#progressBar').addClass('hide'); $('#installationError').removeClass('hide'); }).done(function(data){ $('#progressBar .progress-bar').css('width', data.percent + '%').attr('aria-valuenow', data.percent).html(data.percent + '%'); $('#installationProgress .list-group').prepend('<li class=\"list-group-item\"><strong>' + data.table + '</strong> ' + data.result + '</li>'); if (data.percent < 100) nextStep(++step, version); else { $('#progressBar .progress-bar').removeClass('active progress-bar-striped'); if (data.error) $('#installationFinishedError').removeClass('hide'); else $('#installationFinished').removeClass('hide'); }}); }; $('#installPodium').click(function(e){ e.preventDefault(); $('#startInstallation').addClass('hide'); $('#installationResults').removeClass('hide'); $('#progressBar .progress-bar').css('width', '10px'); nextStep(0, '$dbVersion'); });");

?>
<div class="row" id="startInstallation">
    <div class="text-center col-sm-12">
<?php if ($error == '' && $info == ''): ?>
        <em><?= Yii::t('podium/view', 'Podium will attempt to update all database tables to the current version.') ?></em><br>
        <em><strong class="text-danger"><?= Yii::t('podium/view', 'Back up your existing database and then click the button below.') ?></strong></em><br><br>
        <div class="alert alert-danger">
            <span class="glyphicon glyphicon-alert"></span> <?= Yii::t('podium/view', 'Seriously - back up your existing database first!') ?><br>
            <?= Yii::t('podium/view', 'Podium does its best to make sure your data is not corrupted but make a database copy just in case.') ?><br>
            <?= Yii::t('podium/view', 'You have been warned!') ?>*
        </div>
        <button id="installPodium" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-open"></span> <?= Yii::t('podium/view', 'Upgrade Podium Database') ?></button><br><br>
<?php elseif ($error != ''): ?>
        <div class="alert alert-danger"><?= $error ?></div>
<?php elseif ($info != ''): ?>
        <div class="alert alert-success"><?= $info ?></div>
<?php endif; ?>
        <?= Yii::t('podium/view', 'Current database version') ?> <kbd><?= $dbVersion ?></kbd> <span class="glyphicon glyphicon-transfer"></span> <kbd><?= $currentVersion ?></kbd> <?= Yii::t('podium/view', 'Current module version') ?>
<?php if ($info == ''): ?>
        <br><br>
        <small>* <?= Yii::t('podium/view', 'Podium cannot be held liable for any database damages that may result directly or indirectly from the updating process. Back up your data first!') ?></small>
<?php endif; ?>
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
            <button class="btn btn-danger btn-lg"><span class="glyphicon glyphicon-alert"></span> <?= Yii::t('podium/view', 'Errors during update') ?></button>
        </div>
    </div><br>
    <div class="col-sm-8 col-sm-offset-2" id="installationProgress">
        <ul class="list-group"></ul>
    </div>
</div>
