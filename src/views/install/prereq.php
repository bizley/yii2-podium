<?php
$this->title                   = Yii::t('podium/view', 'Podium Installation Prerequirements');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Podium Installation'), 'url' => ['install/run']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-danger">
            <span class="glyphicon glyphicon-exclamation-sign"></span> <?= Yii::t('podium/view', 'Before you install/upgrade this module please make sure you have added {MODE} parameter with {INSTALL} value for Podium in your configuration.', ['MODE' => '<code>mode</code>', 'INSTALL' => '<code>INSTALL</code>']) ?>
        </div>
        <pre>
'modules' => [
    'podium' => [
        'class' => 'bizley\podium\Podium',
        'params' => [
            'mode' => 'INSTALL'
        ]
    ],
],</pre>
        <div class="alert alert-danger">
            <span class="glyphicon glyphicon-alert"></span> <strong><?= Yii::t('podium/view', 'After the installation process is finished make sure you remove this parameter.') ?></strong><br><?= Yii::t('podium/view', 'Otherwise anyone can access Podium installation and create administrator account with default login and password.') ?>
        </div>
    </div>
</div>