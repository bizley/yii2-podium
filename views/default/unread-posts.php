<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\models\ThreadView;
use yii\widgets\ListView;

$this->title = Yii::t('podium/view', 'Unread posts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="unreadThreads">
        <h4 class="panel-title"><?= Yii::t('podium/view', 'Unread posts') ?></h4>
    </div>
    <div id="collapseUnread" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="unreadThreads">
        <table class="table table-hover">
            <?= $this->render('/elements/forum/_thread_header') ?>
            <?= ListView::widget([
                'dataProvider'     => (new ThreadView)->search(),
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