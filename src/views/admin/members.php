<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\models\User;
use bizley\podium\widgets\gridview\ActionColumn;
use bizley\podium\widgets\gridview\GridView;
use bizley\podium\widgets\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Forum Members');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('#podiumModalDelete').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#deleteUrl').attr('href', button.data('url')); });");
$this->registerJs("$('#podiumModalBan').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#banUrl').attr('href', button.data('url')); });");
$this->registerJs("$('#podiumModalUnBan').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#unbanUrl').attr('href', button.data('url')); });");

$loggedId = User::loggedId();

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'members']); ?>
<br>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'id',
            'label' => Yii::t('podium/view', 'ID'),
            'contentOptions' => ['class' => 'col-sm-1 text-right'],
            'headerOptions' => ['class' => 'col-sm-1 text-right'],
        ],
        [
            'attribute' => 'username',
            'label' => Yii::t('podium/view', 'Username'),
        ],
        [
            'attribute' => 'email',
            'label' => Yii::t('podium/view', 'E-mail'),
            'format' => 'raw',
            'value' => function ($model) {
                return Html::mailto($model->email);
            },
        ],
        [
            'attribute' => 'role',
            'label' => Yii::t('podium/view', 'Role'),
            'filter' => User::getRoles(),
            'value' => function ($model) {
                return ArrayHelper::getValue(User::getRoles(), $model->role);
            },
        ],
        [
            'attribute' => 'status',
            'label' => Yii::t('podium/view', 'Status'),
            'filter' => User::getStatuses(),
            'value' => function ($model) {
                return ArrayHelper::getValue(User::getStatuses(), $model->status);
            },
        ],
        [
            'class' => ActionColumn::className(),
            'template' => '{view} {pm} {ban} {delete}',
            'buttons' => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ActionColumn::buttonOptions([
                        'title' => Yii::t('podium/view', 'View Member')
                    ]));
                },
                'pm' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ActionColumn::buttonOptions([
                            'title' => Yii::t('podium/view', 'Send Message')
                        ]));
                    }
                    return ActionColumn::mutedButton('glyphicon glyphicon-envelope');
                },
                'ban' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        if ($model->status !== User::STATUS_BANNED) {
                            return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-ban-circle"></span>', ActionColumn::buttonOptions([
                                'class' => 'btn btn-danger btn-xs',
                                'title' => Yii::t('podium/view', 'Ban Member')
                            ])), ['data-toggle' => 'modal', 'data-target' => '#podiumModalBan', 'data-url' => $url]);
                        }
                        return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-ok-circle"></span>', ActionColumn::buttonOptions([
                            'class' => 'btn btn-success btn-xs',
                            'title' => Yii::t('podium/view', 'Unban Member')
                        ])), ['data-toggle' => 'modal', 'data-target' => '#podiumModalUnBan', 'data-url' => $url]);
                    }
                    return ActionColumn::mutedButton('glyphicon glyphicon-ban-circle');
                },
                'delete' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-trash"></span>', ActionColumn::buttonOptions([
                            'class' => 'btn btn-danger btn-xs',
                            'title' => Yii::t('podium/view', 'Delete Member')
                        ])), ['data-toggle' => 'modal', 'data-target' => '#podiumModalDelete', 'data-url' => $url]);
                    }
                    return ActionColumn::mutedButton('glyphicon glyphicon-trash');
                },
            ],
        ]
    ],
]); ?>

<?php Modal::begin([
    'id' => 'podiumModalDelete',
    'header' => Yii::t('podium/view', 'Delete User'),
    'footer' => Yii::t('podium/view', 'Delete User'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger', 'id' => 'deleteUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to delete this user?') ?></p>
<p><?= Yii::t('podium/view', 'The user can register again using the same name but all previously created posts will not be linked back to him.') ?></p>
<p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
<?php Modal::end() ?>
<?php Modal::begin([
    'id' => 'podiumModalBan',
    'header' => Yii::t('podium/view', 'Ban User'),
    'footer' => Yii::t('podium/view', 'Ban User'),
    'footerConfirmOptions' => ['class' => 'btn btn-danger', 'id' => 'banUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to ban this user?') ?></p>
<p><?= Yii::t('podium/view', 'The user will not be deleted but will not be able to sign in again.') ?></p>
<p><strong><?= Yii::t('podium/view', 'You can always unban the user if you change your mind later on.') ?></strong></p>
<?php Modal::end() ?>
<?php Modal::begin([
    'id' => 'podiumModalUnBan',
    'header' => Yii::t('podium/view', 'Unban User'),
    'footer' => Yii::t('podium/view', 'Unban User'),
    'footerConfirmOptions' => ['class' => 'btn btn-success', 'id' => 'unbanUrl']
 ]) ?>
<p><?= Yii::t('podium/view', 'Are you sure you want to unban this user?') ?></p>
<p><?= Yii::t('podium/view', 'The user will be able to sign in again.') ?></p>
<?php Modal::end();
