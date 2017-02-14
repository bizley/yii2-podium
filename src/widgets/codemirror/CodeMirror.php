<?php

namespace bizley\podium\widgets\codemirror;

use bizley\podium\widgets\codemirror\assets\CodeMirrorAsset;
use Yii;
use yii\bootstrap\Html;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Podium CodeMirror widget.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class CodeMirror extends InputWidget
{
    /**
     * @var string Editor type to display
     */
    public $type = 'basic';

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            if (empty($this->model->{$this->attribute})) {
                $this->model->{$this->attribute} = "\n\n\n\n\n\n\n\n";
            }
            return Html::activeTextarea($this->model, $this->attribute, ['id' => 'codemirror']);
        }
        if (empty($this->value)) {
            $this->value = "\n\n\n\n\n\n\n\n";
        }
        return Html::textarea($this->name, $this->value, ['id' => 'codemirror']);
    }

    /**
     * Registers widget assets.
     * Note that CodeMirror works without jQuery.
     */
    public function registerClientScript()
    {
        $view = $this->view;
        CodeMirrorAsset::register($view);
        $js = 'var CodeMirrorLabels = {
    bold: "' . Yii::t('podium/view', 'Bold') . '",
    italic: "' . Yii::t('podium/view', 'Italic') . '",
    header: "' . Yii::t('podium/view', 'Header') . '",
    inlinecode: "' . Yii::t('podium/view', 'Inline code') . '",
    blockcode: "' . Yii::t('podium/view', 'Block code') . '",
    quote: "' . Yii::t('podium/view', 'Quote') . '",
    bulletedlist: "' . Yii::t('podium/view', 'Bulleted list') . '",
    orderedlist: "' . Yii::t('podium/view', 'Ordered list') . '",
    link: "' . Yii::t('podium/view', 'Link') . '",
    image: "' . Yii::t('podium/view', 'Image') . '",
    help: "' . Yii::t('podium/view', 'Help') . '",
};var CodeMirrorSet = "' . $this->type . '";';
        $view->registerJs($js, View::POS_BEGIN);
    }
}
