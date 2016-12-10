<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\Podium;
use bizley\podium\widgets\Avatar;
use yii\helpers\Html;

?>
<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => $reply->reply->sender]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><span data-toggle="tooltip" data-placement="top" title="<?= Podium::getInstance()->formatter->asDatetime($reply->reply->created_at, 'long') ?>"><?= Podium::getInstance()->formatter->asRelativeTime($reply->reply->created_at) ?></span></small>
                <?= Html::encode($reply->reply->topic) ?>
            </div>
            <div class="popover-content">
                <?= $reply->reply->parsedContent ?>
            </div>
        </div>
    </div>
</div>