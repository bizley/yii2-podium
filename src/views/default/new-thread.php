<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */

use bizley\quill\Quill;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;

$this->title = Yii::t('podium/view', 'New Thread');
$this->params['breadcrumbs'][] = ['label' => Yii::t('podium/view', 'Main Forum'), 'url' => ['default/index']];
$this->params['breadcrumbs'][] = ['label' => $forum->category->name, 'url' => ['default/category', 'id' => $forum->category->id, 'slug' => $forum->category->slug]];
$this->params['breadcrumbs'][] = ['label' => $forum->name, 'url' => ['default/forum', 'cid' => $forum->category->id, 'id' => $forum->id, 'slug' => $forum->slug]];
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
            <?php $form = ActiveForm::begin(['id' => 'new-thread-form']); ?>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'name')->textInput()->label(Yii::t('podium/view', 'Topic')) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'post')->label(false)->widget(Quill::className(), ['options' => ['style' => 'height:320px']]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?= $form->field($model, 'subscribe')->checkbox()->label(Yii::t('podium/view', 'Subscribe to this thread')) ?>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-8">
                            <?= Html::submitButton('<span class="glyphicon glyphicon-ok-sign"></span> ' . Yii::t('podium/view', 'Create new thread'), ['class' => 'btn btn-block btn-primary', 'name' => 'save-button']) ?>
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
