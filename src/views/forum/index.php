<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;
use bizley\podium\widgets\LatestPosts;
use yii\helpers\Url;

$this->title = Yii::t('podium/view', 'Main Forum');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-sm-9">
        <?= $this->render('/elements/forum/_sections', ['dataProvider' => $dataProvider]) ?>
    </div>
    <div class="col-sm-3">
<?php if (!Podium::getInstance()->user->isGuest): ?>
        <a href="<?= Url::to(['forum/unread-posts']) ?>" class="btn btn-info btn-xs btn-block"><span class="glyphicon glyphicon-flash"></span> <?= Yii::t('podium/view', 'Unread posts') ?></a><br>
<?php endif ?>
        <?= LatestPosts::widget(); ?>
    </div>
</div>
<?= $this->render('/elements/main/_members');
