<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\helpers\Helper;
use bizley\podium\models\User;
use bizley\podium\Podium;
use bizley\podium\widgets\gridview\ActionColumn;
use bizley\podium\widgets\gridview\GridView;
use bizley\podium\widgets\Readers;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Moderation Team');
$this->params['breadcrumbs'][] = $this->title;

?>
<ul class="nav nav-tabs">
    <li role="presentation">
        <a href="<?= Url::to(['members/index']) ?>">
            <span class="glyphicon glyphicon-user"></span>
            <?= Yii::t('podium/view', 'Members List') ?>
        </a>
    </li>
    <li role="presentation" class="active">
        <a href="<?= Url::to(['members/mods']) ?>">
            <span class="glyphicon glyphicon-scissors"></span>
            <?= Yii::t('podium/view', 'Moderation Team') ?>
        </a>
    </li>
</ul>
<br>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'username',
            'label' => Yii::t('podium/view', 'Username'),
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model->podiumName, ['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug], ['data-pjax' => '0']);
            },
        ],
        [
            'attribute' => 'role',
            'label' => Yii::t('podium/view', 'Role'),
            'format' => 'raw',
            'filter' => User::getModRoles(),
            'value' => function ($model) {
                return Helper::roleLabel($model->role);
            },
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t('podium/view', 'Joined'),
            'format' => 'datetime'
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{view}' . (!Podium::getInstance()->user->isGuest ? ' {pm}' : ''),
            'buttons' => [
                'view' => function($url, $model) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug], ActionColumn::buttonOptions([
                        'title' => Yii::t('podium/view', 'View Member')
                    ]));
                },
                'pm' => function($url, $model) {
                    if ($model->id !== User::loggedId()) {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ActionColumn::buttonOptions([
                            'title' => Yii::t('podium/view', 'Send Message')
                        ]));
                    }
                    return ActionColumn::mutedButton('glyphicon glyphicon-envelope');
                },
            ],
        ]
    ],
]); ?>
<div class="panel panel-default">
    <div class="panel-body small">
        <ul class="list-inline pull-right">
            <li><a href="<?= Url::to(['forum/index']) ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the main page') ?>"><span class="glyphicon glyphicon-home"></span></a></li>
            <li><a href="#top" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the top') ?>"><span class="glyphicon glyphicon-arrow-up"></span></a></li>
        </ul>
        <?= Readers::widget(['what' => 'members']) ?>
    </div>
</div>
