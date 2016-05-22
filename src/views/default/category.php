<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Url;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php if (!Yii::$app->user->isGuest): ?>
<div class="row">
    <div class="col-sm-12 text-right">
        <ul class="list-inline">
            <li><a href="<?= Url::to(['default/unread-posts']) ?>" class="btn btn-info btn-sm"><span class="glyphicon glyphicon-flash"></span> <?= Yii::t('podium/view', 'Unread posts') ?></a></li>
        </ul>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist" aria-multiselectable="true">
            <?= $this->render('/elements/forum/_section', ['model' => $model]) ?>
        </div>
    </div>
</div>
<?= $this->render('/elements/main/_members');
