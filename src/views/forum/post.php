<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\podium\models\User;
use bizley\podium\widgets\Avatar;
use bizley\podium\widgets\editor\EditorFull;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'New Reply');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['forum/index']];
$this->params['breadcrumbs'][] = ['label' => $thread->forum->category->name, 'url' => ['forum/category', 'id' => $thread->forum->category->id, 'slug' => $thread->forum->category->slug]];
$this->params['breadcrumbs'][] = ['label' => $thread->forum->name, 'url' => ['forum/forum', 'cid' => $thread->forum->category->id, 'id' => $thread->forum->id, 'slug' => $thread->forum->slug]];
$this->params['breadcrumbs'][] = ['label' => $thread->name, 'url' => ['forum/thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;

$author = User::findMe();

?>
<?php if ($preview): ?>
<div class="row">
    <div class="col-sm-10 col-sm-offset-2">
        <?= Alert::widget([
            'body' => '<strong><small>'
                        . Yii::t('podium/view', 'Post Preview')
                        . '</small></strong>:<hr>' . $model->parsedContent,
            'options' => ['class' => 'alert-info']
        ]) ?>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-2 text-center">
        <?= Avatar::widget(['author' => $author, 'showName' => false]) ?>
    </div>
    <div class="col-sm-10">
        <div class="popover right podium">
            <div class="arrow"></div>
            <div class="popover-title">
                <small class="pull-right"><span data-toggle="tooltip" data-placement="bottom" title="<?= Yii::t('podium/view', 'As soon as you click Post Reply') ?>"><?= Yii::t('podium/view', 'In a while') ?></span></small>
                <?= $author->podiumTag ?>
            </div>
            <div class="popover-content podium-content">
                <?php $form = ActiveForm::begin(['id' => 'new-post-form', 'action' => ['post', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'tid' => $thread->id]]); ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'content')->label(false)->widget(EditorFull::className()) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="form-group">
                                <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Post Reply'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= Html::submitButton('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Preview'), ['class' => 'btn btn-block btn-default', 'name' => 'preview-button']) ?>
                            </div>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
<br>
<?= $this->render('/elements/forum/_post', ['model' => $previous, 'category' => $thread->forum->category->id, 'slug' => $thread->slug]) ?>
<br>
