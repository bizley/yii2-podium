<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\models\ThreadView;
use bizley\podium\widgets\Readers;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = Yii::t('podium/view', 'Unread posts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-12 text-right">
        <ul class="list-inline">
            <li><a href="<?= Url::to(['forum/mark-seen']) ?>" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-eye-open"></span> <?= Yii::t('podium/view', 'Mark all as seen') ?></a></li>
        </ul>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="unreadThreads">
        <h4 class="panel-title"><?= Yii::t('podium/view', 'Unread posts') ?></h4>
    </div>
    <div id="collapseUnread" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="unreadThreads">
        <table class="table table-hover">
            <?= $this->render('/elements/forum/_thread_header') ?>
            <?= ListView::widget([
                'dataProvider'     => (new ThreadView())->search(),
                'itemView'         => '/elements/forum/_thread',
                'summary'          => '',
                'emptyText'        => Yii::t('podium/view', 'No more unread posts at the moment.'),
                'emptyTextOptions' => ['tag' => 'td', 'class' => 'text-muted', 'colspan' => 4],
                'options'          => ['tag' => 'tbody'],
                'itemOptions'      => ['tag' => 'tr', 'class' => 'podium-thread-line']
            ]); ?>
        </table>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body small">
        <ul class="list-inline pull-right">
            <li><a href="<?= Url::to(['forum/index']) ?>" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the main page') ?>"><span class="glyphicon glyphicon-home"></span></a></li>
            <li><a href="#top" data-toggle="tooltip" data-placement="top" title="<?= Yii::t('podium/view', 'Go to the top') ?>"><span class="glyphicon glyphicon-arrow-up"></span></a></li>
        </ul>
        <?= Readers::widget(['what' => 'unread']) ?>
    </div>
</div>