<?php

namespace bizley\podium\widgets\codemirror;

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
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            return Html::activeTextarea($this->model, $this->attribute, ['id' => 'codemirror']);
        }
        return Html::textarea($this->name, $this->value, ['id' => 'codemirror']);
    }
    
    /**
     * Registers widget assets.
     * Note that Quill works without jQuery.
     */
    public function registerClientScript()
    {
        $view = $this->view;
        CodeMirrorAsset::register($view);
        
        $js = 'CodeMirror.findModeByName("php").mime="text/x-php";var CodeMirrorEditor=CodeMirror.fromTextArea(document.getElementById("codemirror"), {
    mode: "gfm",
    theme: "default",
    extraKeys: {
        "Enter": "newlineAndIndentContinueMarkdownList",
    },
    lineNumbers: false,
    autofocus: true,
    matchBrackets: true,
    autoCloseBrackets: true,
    autoCloseTags: true,
    buttons: [
        {
            hotkey: "Ctrl-B",
            class: "bold",
            label: "<strong>B</strong>",
            callback: function (cm) {
                var selection = cm.getSelection();
                cm.replaceSelection("**" + selection + "**");
                if (!selection) {
                    var cursorPos = cm.getCursor();
                    cm.setCursor(cursorPos.line, cursorPos.ch - 2);
                }
            }
        },
        {
            hotkey: "Ctrl-I",
            class: "italic",
            label: "<i>I</i>",
            callback: function (cm) {
                var selection = cm.getSelection();
                cm.replaceSelection("*" + selection + "*");
                if (!selection) {
                    var cursorPos = cm.getCursor();
                    cm.setCursor(cursorPos.line, cursorPos.ch - 1);
                }
            }
        },
        {
            class: "inline-code",
            label: "code",
            callback: function (cm) {
                var selection = cm.getSelection();
                cm.replaceSelection("`" + selection + "`");
                if (!selection) {
                    var cursorPos = cm.getCursor();
                    cm.setCursor(cursorPos.line, cursorPos.ch - 1);
                }
            }
        },
        {
            class: "block-php",
            label: "&lt;php&gt;",
            callback: function (cm) {
                var selection = cm.getSelection();
                cm.replaceSelection("```php\n<?php\n" + selection + "\n```\n");
                if (!selection) {
                    var cursorPos = cm.getCursor();
                    cm.setCursor(cursorPos.line - 2, 0);
                }
            }
        },
        {
            class: "block-code",
            label: "&lt;-&gt;",
            callback: function (cm) {
                var selection = cm.getSelection();
                cm.replaceSelection("```\n" + selection + "\n```\n");
                if (!selection) {
                    var cursorPos = cm.getCursor();
                    cm.setCursor(cursorPos.line - 2, 0);
                }
            }
        },
        {
            class: "quote",
            label: ">",
            callback: function (cm) {
                cm.replaceSelection("> " + cm.getSelection());
            }
        },
        {
            class: "ul",
            label: "ul",
            callback: function (cm) {
                cm.replaceSelection("- " + cm.getSelection());
            }
        },
        {
            class: "ol",
            label: "ol",
            callback: function (cm) {
                cm.replaceSelection("1. " + cm.getSelection());
            }
        },
        {
            class: "a",
            label: "a",
            callback: function (cm) {
                var selection = cm.getSelection();
                var text = "";
                var link = "";

                if (selection.match(/^https?:\/\//)) {
                    link = selection;
                } else {
                    text = selection;
                }
                cm.replaceSelection("[" + text + "](" + link + ")");

                var cursorPos = cm.getCursor();
                if (!selection) {
                    cm.setCursor(cursorPos.line, cursorPos.ch - 3);
                } else if (link) {
                    cm.setCursor(cursorPos.line, cursorPos.ch - (3 + link.length));
                } else {
                    cm.setCursor(cursorPos.line, cursorPos.ch - 1);
                }
            }
        }
    ],
});';
        $view->registerJs($js, View::POS_END);
    }
}
