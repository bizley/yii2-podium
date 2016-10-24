<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

?>
<table class="table table-hover">
    <?= $this->render('/elements/forum/_thread_header') ?>
    <?= $this->render('/elements/members/_thread_list', ['id' => $id]) ?>
</table>
