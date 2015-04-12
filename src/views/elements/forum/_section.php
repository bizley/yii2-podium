<?php

use yii\helpers\Html;
use yii\helpers\Url;

?><div class="panel panel-default">
    <div class="panel-heading" role="tab" id="category<?= $model->id ?>">
        <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapse<?= $model->id ?>" aria-expanded="true" aria-controls="collapse<?= $model->id ?>" class="pull-right">
                <span class="glyphicon glyphicon-chevron-up"></span>
            </a>
            <a href="<?= Url::to(['category', 'id' => $model->id, 'slug' => $model->slug]) ?>"><?= Html::encode($model->name) ?></a>
        </h4>
    </div>
    <div id="collapse<?= $model->id ?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="category<?= $model->id ?>">
        <?= $this->render('/elements/forum/_forums', ['category' => $model->id]) ?>
    </div>
</div>