<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use bizley\podium\widgets\PageSizer;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = Yii::t('podium/view', 'Forum Members');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Administration Dashboard'), 'url' => ['admin/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("$('[data-toggle=\"tooltip\"]').tooltip();");
$this->registerJs("$('#podiumModalDelete').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#deleteUrl').attr('href', button.data('url')); });");
$this->registerJs("$('#podiumModalBan').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#banUrl').attr('href', button.data('url')); });");
$this->registerJs("$('#podiumModalUnBan').on('show.bs.modal', function(e) { var button = $(e.relatedTarget); $('#unbanUrl').attr('href', button.data('url')); });");

$loggedId = User::loggedId();

?>
<?= $this->render('/elements/admin/_navbar', ['active' => 'members']); ?>
<br>
<?php Pjax::begin(); ?>
<?= PageSizer::widget() ?>
<?= GridView::widget([
    'dataProvider'   => $dataProvider,
    'filterModel'    => $searchModel,
    'filterSelector' => 'select#per-page',
    'tableOptions'   => ['class' => 'table table-striped table-hover'],
    'columns'        => [
        [
            'attribute'      => 'id',
            'label'          => Yii::t('podium/view', 'ID') . Helper::sortOrder('id'),
            'encodeLabel'    => false,
            'contentOptions' => ['class' => 'col-sm-1 text-right'],
            'headerOptions'  => ['class' => 'col-sm-1 text-right'],
        ],
        [
            'attribute'   => 'username',
            'label'       => Yii::t('podium/view', 'Username') . Helper::sortOrder('username'),
            'encodeLabel' => false,
        ],
        [
            'attribute'   => 'email',
            'label'       => Yii::t('podium/view', 'E-mail') . Helper::sortOrder('email'),
            'encodeLabel' => false,
            'format'      => 'raw',
            'value'       => function ($model) {
                return Html::mailto($model->email);
            },
        ],
        [
            'attribute'   => 'role',
            'label'       => Yii::t('podium/view', 'Role') . Helper::sortOrder('role'),
            'encodeLabel' => false,
            'filter'      => User::getRoles(),
            'value'       => function ($model) {
                return Yii::t('podium/view', ArrayHelper::getValue(User::getRoles(), $model->role));
            },
        ],
        [
            'attribute'   => 'status',
            'label'       => Yii::t('podium/view', 'Status') . Helper::sortOrder('status'),
            'encodeLabel' => false,
            'filter'      => User::getStatuses(),
            'value'       => function ($model) {
                return Yii::t('podium/view', ArrayHelper::getValue(User::getStatuses(), $model->status));
            },
        ],
        [
            'class'          => ActionColumn::className(),
            'header'         => Yii::t('podium/view', 'Actions'),
            'contentOptions' => ['class' => 'text-right'],
            'headerOptions'  => ['class' => 'text-right'],
            'template'       => '{view} {pm} {ban} {delete}',
            'buttons'        => [
                'view' => function($url) {
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $url, ['class' => 'btn btn-default btn-xs', 'data-pjax' => '0', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'View Member')]);
                },
                'pm' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', ['messages/new', 'user' => $model->id], ['class' => 'btn btn-default btn-xs', 'data-pjax' => '0', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Send Message')]);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-envelope"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                    }
                },
                'ban' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        if ($model->status !== User::STATUS_BANNED) {
                            return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-ban-circle"></span>', ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Ban Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalBan', 'data-url' => $url]);
                        }
                        else {
                            return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-ok-circle"></span>', ['class' => 'btn btn-success btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Unban Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalUnBan', 'data-url' => $url]);
                        }
                    }
                    return Html::a('<span class="glyphicon glyphicon-ban-circle"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                },
                'delete' => function($url, $model) use ($loggedId) {
                    if ($model->id !== $loggedId) {
                        return Html::tag('span', Html::tag('button', '<span class="glyphicon glyphicon-trash"></span>', ['class' => 'btn btn-danger btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('podium/view', 'Delete Member')]), ['data-toggle' => 'modal', 'data-target' => '#podiumModalDelete', 'data-url' => $url]);
                    }
                    else {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', '#', ['class' => 'btn btn-xs disabled text-muted']);
                    }
                },
            ],
        ]
    ],
]); ?>
<?php Pjax::end(); ?>

<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalDeleteLabel" aria-hidden="true" id="podiumModalDelete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalDeleteLabel"><?= Yii::t('podium/view', 'Delete user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to delete this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user can register again using the same name but all previously created posts will not be linked back to him.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'This action can not be undone.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="deleteUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Delete user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalBanLabel" aria-hidden="true" id="podiumModalBan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalBanLabel"><?= Yii::t('podium/view', 'Ban user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to ban this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will not be deleted but will not be able to sign in again.') ?></p>
                <p><strong><?= Yii::t('podium/view', 'You can always unban the user if you change your mind later on.') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="banUrl" class="btn btn-danger"><?= Yii::t('podium/view', 'Ban user') ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="podiumModalUnBanLabel" aria-hidden="true" id="podiumModalUnBan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="podiumModalUnBanLabel"><?= Yii::t('podium/view', 'Unban user') ?></h4>
            </div>
            <div class="modal-body">
                <p><?= Yii::t('podium/view', 'Are you sure you want to unban this user?') ?></p>
                <p><?= Yii::t('podium/view', 'The user will be able to sign in again.') ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('podium/view', 'Cancel') ?></button>
                <a href="#" id="unbanUrl" class="btn btn-success"><?= Yii::t('podium/view', 'Unban user') ?></a>
            </div>
        </div>
    </div>
</div>
