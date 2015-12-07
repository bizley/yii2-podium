<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */

use bizley\podium\components\Helper;
use dosamigos\ckeditor\CKEditor;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'Edit Post');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($category->name), 'url' => ['default/category', 'id' => $category->id, 'slug' => $category->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($forum->name), 'url' => ['default/forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($thread->name), 'url' => ['default/thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php if (!empty($preview)): ?>
<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <?= Alert::widget(['body' => '<strong><small>' . Yii::t('podium/view', 'Post Preview') . '</small></strong>:<hr>' . $preview, 'options' => ['class' => 'alert-info']]); ?>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-10 col-sm-offset-1">
        <div class="panel panel-default">
            <?php $form = ActiveForm::begin(['id' => 'edit-post-form']); ?>
                <div class="panel-body">
<?php if ($isFirstPost): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'topic')->textInput()->label(Yii::t('podium/view', 'Topic')) ?>
                        </div>
                    </div>
<?php endif; ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'content')->label(false)->widget(CKEditor::className(), [
                                'clientOptions' => Helper::ckEditorOptions('full'),
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-8">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Save Post'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-eye-open"></span> ' . Yii::t('podium/view', 'Preview'), ['class' => 'btn btn-block btn-default', 'name' => 'preview-button']) ?>
                        </div>
                    </div>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div><br>
