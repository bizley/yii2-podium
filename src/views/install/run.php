<?php
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Progress;

$js = "var nextStep = function(step){
    jQuery.ajax({
        url: '" . Url::to(['install/import']) . "',
        method: 'POST',
        data: {step: step},
        dataType: 'json'
    }).fail(function(){
        jQuery('#progressBar').addClass('hide');
        jQuery('#installationError').removeClass('hide');
    }).done(function(data){
        jQuery('#progressBar .progress-bar').css('width', data.percent+'%').attr('aria-valuenow', data.percent).html(data.percent+'%');
        jQuery('#installationProgress .list-group').append('<li class=\"list-group-item\"><strong>'+data.table+'</strong> '+data.result+'</li>');
        if (data.percent < 100) nextStep(++step);
        else {
            jQuery('#progressBar .progress-bar').removeClass('active progress-bar-striped');
            if (data.error) jQuery('#installationFinishedWarning').removeClass('hide');
            else jQuery('#installationFinished').removeClass('hide');
        }
    });
};
jQuery('#installPodium').click(function(e){
    e.preventDefault();
    jQuery('#startInstallation').addClass('hide');
    jQuery('#installationResults').removeClass('hide');
    jQuery('#progressBar .progress-bar').css('width', '10pxs');
    nextStep(0);
});";

$this->registerJs($js, View::POS_READY, 'podium-install');

$this->title                   = Yii::t('podium/view', 'Podium Installation');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row" id="startInstallation">
    <div class="text-center col-sm-12">
        <em><?= Yii::t('podium/view', 'Podium will attempt to create all database tables required by the forum along with the administration account and sample posts. Pre-existing data will not be removed.') ?></em><br><br>
        <button id="installPodium" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-import"></span> <?= Yii::t('podium/view', 'Create database tables required by Podium') ?></button>
    </div>
</div>
<div class="row hide" id="installationResults">
    <div class="text-center col-sm-8 col-sm-offset-2" id="progressBar">
        <?= Progress::widget([
            'percent' => 0,
            'label' => '0%',
            'barOptions' => ['class' => 'progress-bar progress-bar-striped active'],
            'options' => ['class' => 'progress']
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
    <div class="row hide" id="installationFinishedWarning">
        <div class="text-center col-sm-12">
            <a href="<?= Url::to(['default/index']) ?>" class="btn btn-warning btn-lg"><span class="glyphicon glyphicon-ok-sign"></span> <?= Yii::t('podium/view', 'Installation finished with warnings') ?></a>
        </div>
    </div><br>
    <div class="col-sm-8 col-sm-offset-2" id="installationProgress">
        <ul class="list-group"></ul>
    </div>
</div>