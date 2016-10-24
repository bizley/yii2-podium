<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

?>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="memberThreads">
        <h4 class="panel-title">
            <?= Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->podiumName]) ?>
        </h4>
    </div>
    <div id="collapseThreads" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="memberThreads">
        <?= $this->render('/elements/members/_threads', ['id' => $user->id]) ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body small">
        <?= $this->render('/elements/forum/_icons') ?>
    </div>
</div>
