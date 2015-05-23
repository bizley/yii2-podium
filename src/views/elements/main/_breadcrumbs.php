<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
?>

<div class="row">
    <div class="col-sm-9">
        <?= Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []]); ?>
    </div>
    <div class="col-sm-3">
        <?= Html::beginForm(['default/search'], 'get'); ?>
        <div class="input-group">
            <?= Html::textInput('query', null, ['class' => 'form-control']); ?>
            <div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="glyphicon glyphicon-search"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="<?= Url::to(['default/search']) ?>"><?= Yii::t('podium/layout', 'Search Form') ?></a></li>
                </ul>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>
</div>