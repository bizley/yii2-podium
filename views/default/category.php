<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use yii\helpers\Html;

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist" aria-multiselectable="true">
            <?= $this->render('/elements/forum/_section', ['model' => $model]) ?>
        </div>
    </div>
</div>
<?= $this->render('/elements/main/_members');
