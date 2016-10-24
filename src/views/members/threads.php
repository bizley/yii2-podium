<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->podiumName]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['members/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Member View'), 'url' => ['members/view', 'id' => $user->id, 'slug' => $user->podiumSlug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<ul class="nav nav-tabs">
    <li role="presentation"><a href="<?= Url::to(['members/index']) ?>"><span class="glyphicon glyphicon-user"></span> <?= Yii::t('podium/view', 'Members List') ?></a></li>
    <li role="presentation"><a href="<?= Url::to(['members/mods']) ?>"><span class="glyphicon glyphicon-scissors"></span> <?= Yii::t('podium/view', 'Moderation Team') ?></a></li>
    <li role="presentation"><a href="<?= Url::to(['members/view', 'id' => $user->id, 'slug' => $user->podiumSlug]) ?>"><span class="glyphicon glyphicon-eye-open"></span> <?= Yii::t('podium/view', 'Member View') ?></a></li>
    <li role="presentation" class="active"><a href="#"><span class="glyphicon glyphicon-comment"></span> <?= Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->podiumName]) ?></a></li>
</ul>
<br>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/members/_members_threads', ['user' => $user]) ?>
        </div>
    </div>
</div>
