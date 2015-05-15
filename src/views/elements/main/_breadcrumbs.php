<?php

use yii\widgets\Breadcrumbs;
?>

<div class="row">
    <div class="col-sm-9">
        <?= Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []]); ?>
    </div>
    <div class="col-sm-3">
        <div class="input-group">
            <input type="text" class="form-control">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span>
        </div>
    </div>
</div>