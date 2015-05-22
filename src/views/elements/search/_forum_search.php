<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><div class="panel panel-default">
    <div class="panel-heading" role="tab" id="forumSearch">
        <h4 class="panel-title">
            Search forum.......
        </h4>
    </div>
    <div id="collapseSearch" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="forumSearch">
        <?= $this->render('/elements/search/_threads_search', ['dataProvider' => $dataProvider]) ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body small">
        <?= $this->render('/elements/forum/_icons') ?>
    </div>
</div>