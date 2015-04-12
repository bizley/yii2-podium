<?php
use bizley\podium\components\Config;
?>
<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Config::getInstance()->get('name') ?> <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>