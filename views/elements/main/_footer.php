<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
?>
<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= \bizley\podium\components\Config::getInstance()->get('name') ?> <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>