<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

$this->title = Yii::t('podium/view', 'You have been banned!');

?>
<div class="jumbotron">
    <span style="font-size:5em" class="pull-right glyphicon glyphicon-eye-close"></span>
    <h1><?= $this->title ?></h1>
    <p><?= Yii::t('podium/view', 'Contact the administrator if you would like to get more details about your ban.') ?></p>
</div>
