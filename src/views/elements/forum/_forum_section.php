<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><div class="panel panel-default">
    <div class="panel-heading" role="tab" id="forum<?= $model->id ?>">
        <h4 class="panel-title">
            <a href="<?= Url::to(['forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>"><?= Html::encode($model->name) ?></a>
        </h4>
    </div>
    <div id="threads<?= $model->id ?>" role="tabpanel" aria-labelledby="forum<?= $model->id ?>">
        <?= $this->render('/elements/forum/_threads', ['forum' => $model->id]) ?>
    </div>
</div>