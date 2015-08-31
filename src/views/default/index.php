<?php

$this->title                   = Yii::t('podium/view', 'Main Forum');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-9">
        <?= $this->render('/elements/forum/_sections', ['dataProvider' => $dataProvider]) ?>
    </div>
    <div class="col-sm-3">
        <?= $this->render('/elements/misc/_latest', ['latest' => $latest]) ?>
    </div>
</div>
<?= $this->render('/elements/main/_members') ?>