<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
use bizley\podium\models\Post;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;

$this->title                   = Yii::t('podium/view', 'Posts created by {name}', ['name' => $user->getPodiumName()]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Members List'), 'url' => ['members/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Member View'), 'url' => ['members/view', 'id' => $user->id, 'slug' => $user->slug]];
$this->params['breadcrumbs'][] = $this->title;

echo Html::beginTag('ul', ['class' => 'nav nav-tabs']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-user"></span> ' . Yii::t('podium/view', 'Members List'), ['members/index']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-scissors"></span> ' . Yii::t('podium/view', 'Moderation Team'), ['members/mods']), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Member View'), ['members/view', 'id' => $user->id, 'slug' => $user->slug]), ['role' => 'presentation']);
echo Html::tag('li', Html::a('<span class="glyphicon glyphicon-comment"></span> ' . Yii::t('podium/view', 'Posts created by {name}', ['name' => $user->getPodiumName()]), ''), ['role' => 'presentation', 'class' => 'active']);
echo Html::endTag('ul');

?>

<br>
<?php Pjax::begin();
echo ListView::widget([
    'dataProvider' => (new Post)->searchByUser($user->id),
    'itemView' => '/elements/forum/_post',
    'viewParams' => ['parent' => true],
    'summary' => '',
    'emptyText' => Yii::t('podium/view', 'No posts have been added yet.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]); 
Pjax::end(); ?>
<br>