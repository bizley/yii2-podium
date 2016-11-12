<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;

?>
<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Podium::getInstance()->podiumConfig->get('name') ?> <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>
