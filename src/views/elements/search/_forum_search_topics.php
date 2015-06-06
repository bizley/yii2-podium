<?php

use yii\helpers\Html;

?>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="forumSearch">
        <h4 class="panel-title">
            <?= Yii::t('podium/view', 'Search for "{query}" in {type}', ['query' => Html::encode($query), 'type' => $type == 'topics' ? 'threads' : 'posts']) ?>
        </h4>
    </div>
    <div id="collapseSearch" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="forumSearch">
        <?= $this->render('/elements/search/_threads_search_topics', ['dataProvider' => $dataProvider, 'type' => $type]) ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body small">
        <?= $this->render('/elements/forum/_icons') ?>
    </div>
</div>
