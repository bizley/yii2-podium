<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\widgets\Readers;
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="forum<?= $model->id ?>">
        <h4 class="panel-title">
            <a href="<?= Url::to(['forum/forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug]) ?>"><?= Html::encode($model->name) ?></a>
        </h4>
<?php if (!empty($model->sub)): ?>
        <small class="text-muted"><?= Html::encode($model->sub) ?></small>
<?php endif; ?>
    </div>
    <div id="collapse<?= $model->id ?>" class="panel-collapse collapse in table-responsive" role="tabpanel" aria-labelledby="forum<?= $model->id ?>">
        <?= $this->render('/elements/forum/_threads', ['forum' => $model->id, 'category' => $model->category_id, 'slug' => $model->slug, 'filters' => $filters]) ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body small">
        <?= $this->render('/elements/forum/_icons') ?>
        <?= Readers::widget(['what' => 'forum']) ?>
    </div>
</div>
