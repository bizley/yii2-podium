<?php

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
        <ul class="list-inline">
            <li class="text-muted"><button class="btn btn-xs"><span class="glyphicon glyphicon-comment"></span></button> <?= Yii::t('podium/view', 'No New Posts') ?></li>
            <li class="text-muted"><button class="btn btn-xs"><span class="glyphicon glyphicon-leaf"></span></button> <?= Yii::t('podium/view', 'New Posts') ?></li>
            <li class="text-muted"><button class="btn btn-xs"><span class="glyphicon glyphicon-fire"></span></button> <?= Yii::t('podium/view', 'Hot Thread') ?></li>
            <li class="text-muted"><button class="btn btn-xs"><span class="glyphicon glyphicon-lock"></span></button> <?= Yii::t('podium/view', 'Locked Thread') ?></li>
            <li class="text-muted"><button class="btn btn-xs"><span class="glyphicon glyphicon-pushpin"></span></button> <?= Yii::t('podium/view', 'Pinned Thread') ?></li>
            <li class="text-muted"><button class="btn btn-success btn-xs">&nbsp;&nbsp;&nbsp;&nbsp;</button> <?= Yii::t('podium/view', 'New Posts') ?></li>
            <li class="text-muted"><button class="btn btn-warning btn-xs">&nbsp;&nbsp;&nbsp;&nbsp;</button> <?= Yii::t('podium/view', 'Edited Posts') ?></li>
        </ul>
    </div>
</div>