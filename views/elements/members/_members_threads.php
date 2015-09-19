<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="memberThreads">
        <h4 class="panel-title">
            <?= Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->getPodiumName()]) ?>
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