<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

$this->title = Yii::t('podium/view', 'Forum Maintenance');

$this->registerJs("var deg = 0; var rotate = function () { $('#cog1').css({'-webkit-transform':'rotate(' + deg + 'deg)', '-moz-transform':'rotate(' + deg + 'deg)', '-ms-transform':'rotate(' + deg + 'deg)', '-o-transform':'rotate(' + deg + 'deg)'}); $('#cog2').css({'-webkit-transform':'rotate(-' + deg + 'deg)', '-moz-transform':'rotate(-' + deg + 'deg)', '-ms-transform':'rotate(-' + deg + 'deg)', '-o-transform':'rotate(-' + deg + 'deg)'}); deg += 10; if (deg == 360) deg = 0;} window.setInterval(rotate, 100, deg);");
?>
<div class="jumbotron">
    <span id="cog1" style="font-size:5em" class="pull-right glyphicon glyphicon-cog"></span>
    <span id="cog2" style="font-size:8em" class="pull-right glyphicon glyphicon-cog"></span>
    <h1><?= $this->title ?></h1>
    <p><?= Yii::t('podium/view', 'We will get back to you shortly') ?></p>
</div>
