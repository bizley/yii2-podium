<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Html;
use yii\helpers\Url;

?><div class="panel panel-default">
    <div class="panel-heading" role="tab" id="forum<?= $model->id ?>">
        <h4 class="panel-title">
            <a href="<?= Url::to(['forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>"><?= Html::encode($model->name) ?></a>
        </h4>
<?php if (!empty($model->sub)): ?>
        <small class="text-muted"><?= Html::encode($model->sub) ?></small>
<?php endif; ?>
    </div>
    <div id="collapse<?= $model->id ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="forum<?= $model->id ?>">
        <?= $this->render('/elements/forum/_threads', ['forum' => $model->id]) ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body small">
        <a href="" class="pull-right">RSS</a>
        <?= $this->render('/elements/forum/_icons') ?>
    </div>
</div>