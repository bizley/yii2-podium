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

$url = Url::to(['install/update']);
$this->registerJs(<<<JS
var nextStep = function() {
    var label = 'success';
    var bg = '';
    jQuery.post('$url', null, null, 'json')
        .fail(function() {
            jQuery('#progressBar').addClass('hide');
            jQuery('#installationError').removeClass('hide');
        })
        .done(function(data) {
            if (data.type == 2) {
                label = 'danger';
                bg = 'list-group-item-danger';
            } else if (data.type == 1) {
                label = 'warning';
                bg = 'list-group-item-warning';
            }
            jQuery('#progressBar .progress-bar')
                .css('width', data.percent + '%')
                .attr('aria-valuenow', data.percent)
                .html(data.percent + '%');
            var row = '<li class="list-group-item ' + bg + '">'
                + '<span class="pull-right label label-' + label + '">' + data.table + '</span> '
                + data.result
                + '</li>';
            jQuery('#installationProgress .list-group').prepend(row);
            if (data.type == 2) {
                jQuery('#progressBar .progress-bar').removeClass('active progress-bar-striped');
                jQuery('#installationFinishedError').removeClass('hide');
            } else {
                if (data.percent < 100) {
                    nextStep();
                } else {
                    jQuery('#progressBar .progress-bar').removeClass('active progress-bar-striped');
                    jQuery('#installationFinished').removeClass('hide');
                }
            }
        });
};
jQuery('#installPodium').click(function(e) {
    e.preventDefault();
    jQuery('#startInstallation').addClass('hide');
    jQuery('#installationResults').removeClass('hide');
    jQuery('#progressBar .progress-bar').css('width', '10px');
    nextStep();
});
JS
);
?>
<div class="row" id="startInstallation">
    <div class="text-center col-sm-12">
<?php if ($error == '' && $info == ''): ?>
        <div class="form-group">
            <em><?= Yii::t('podium/view', 'Podium will attempt to update all database tables to the current version.') ?></em><br>
            <em><strong class="text-danger"><?= Yii::t('podium/view', 'Back up your existing database and then click the button below.') ?></strong></em>
        </div>
        <div class="alert alert-warning">
            <span class="glyphicon glyphicon-exclamation-sign"></span> <?= Yii::t('podium/view', 'Seriously - back up your existing database first!') ?><br>
            <?= Yii::t('podium/view', 'Podium does its best to make sure your data is not corrupted but make a database copy just in case.') ?><br>
            <?= Yii::t('podium/view', 'You have been warned!') ?>*
        </div>
        <div class="form-group">
            <button id="installPodium" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-open"></span> <?= Yii::t('podium/view', 'Upgrade Podium Database') ?></button>
        </div>
<?php elseif ($error != ''): ?>
        <div class="alert alert-danger"><?= $error ?></div>
<?php elseif ($info != ''): ?>
        <div class="alert alert-success"><?= $info ?></div>
<?php endif; ?>
        <div class="form-group">
            <?= Yii::t('podium/view', 'Current database version') ?> <kbd><?= $dbVersion ?></kbd> <span class="glyphicon glyphicon-transfer"></span> <kbd><?= $currentVersion ?></kbd> <?= Yii::t('podium/view', 'Current module version') ?>
        </div>
<?php if ($info == ''): ?>
        <div class="form-group text-muted">
            <small>* <?= Yii::t('podium/view', 'Podium cannot be held liable for any database damages that may result directly or indirectly from the installing process. Back up your data first!') ?></small>
        </div>
<?php endif; ?>
    </div>
</div>
<div class="row hide" id="installationResults">
    <div class="text-center col-sm-8 col-sm-offset-2" id="progressBar">
        <?= Progress::widget([
            'percent'    => 0,
            'label'      => '0%',
            'barOptions' => ['class' => 'progress-bar progress-bar-striped active', 'style' => 'min-width: 2em;'],
            'options'    => ['class' => 'progress']
        ]) ?>
    </div>
    <div class="col-sm-8 col-sm-offset-2 hide" id="installationError">
        <div class="alert alert-danger" role="alert"><?= Yii::t('podium/view', 'There was a major error during update...') ?></div>
    </div>
    <div class="row hide" id="installationFinished">
        <div class="text-center col-sm-12">
            <a href="<?= Url::to(['forum/index']) ?>" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-ok-sign"></span> <?= Yii::t('podium/view', 'Update finished') ?></a>
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
