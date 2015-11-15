<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use yii\helpers\Html;

$this->title                   = Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->getPodiumName()]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['members/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Member View'), 'url' => ['members/view', 'id' => $user->id, 'slug' => $user->slug]];
$this->params['breadcrumbs'][] = $this->title;

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members List'), ['members/index']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-scissors"></span> ' . Yii::t('podium/view', 'Moderation Team'), ['members/mods']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Member View'), ['members/view', 'id' => $user->id, 'slug' => $user->slug]), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-comment"></span> ' . Yii::t('podium/view', 'Threads started by {name}', ['name' => $user->getPodiumName()]), ''), ['role' => 'presentation', 'class' => 'active']);
echo Html::endTag('ul');

?>

<br>
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group" role="tablist">
            <?= $this->render('/elements/members/_members_threads', ['user' => $user]) ?>
        </div>
    </div>
</div>