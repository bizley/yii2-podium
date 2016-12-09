CodeMirror.findModeByName("php").mime = "text/x-php";
var CodeMirrorEditor = CodeMirror.fromTextArea(document.getElementById("codemirror"), {
    mode: "gfm",
    theme: "default",
    extraKeys: {"Enter": "newlineAndIndentContinueMarkdownList"},
    lineNumbers: false,
    viewportMargin: Infinity,
    autofocus: true,
    matchBrackets: true,
    autoCloseBrackets: true,
    autoCloseTags: true,
    buttons: [
        {
            hotkey: "Ctrl-B",
            label: "<span class=\"glyphicon glyphicon-bold\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.bold + "\"></span></div>",
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
            label: "<span class=\"glyphicon glyphicon-italic\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.italic + "\"></span>",
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
            label: "<span class=\"glyphicon glyphicon-header\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.header + "\"></span>",
            callback: function (cm) {
                cm.replaceSelection("# " + cm.getSelection());
            }
        },
        {
            label: "<span class=\"glyphicon glyphicon-option-horizontal\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.inlinecode + "\"></span>",
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
            label: "<span class=\"glyphicon glyphicon-option-vertical\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.blockcode + "\"></span>",
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
            label: "<span class=\"glyphicon glyphicon-console\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.quote + "\"></span>",
            callback: function (cm) {
                cm.replaceSelection("> " + cm.getSelection());
            }
        },
        {
            label: "<span class=\"glyphicon glyphicon-list\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.bulletedlist + "\"></span>",
            callback: function (cm) {
                cm.replaceSelection("- " + cm.getSelection());
            }
        },
        {
            label: "<span class=\"glyphicon glyphicon-list-alt\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.orderedlist + "\"></span>",
            callback: function (cm) {
                cm.replaceSelection("1. " + cm.getSelection());
            }
        },
        {
            label: "<span class=\"glyphicon glyphicon-link\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.link + "\"></span>",
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
        },
        {
            label: "<span class=\"glyphicon glyphicon-picture\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.image + "\"></span>",
            callback: function (cm) {
                var selection = cm.getSelection();
                var alt = "";
                var link = "";

                if (selection.match(/^https?:\/\//)) {
                    link = selection;
                } else {
                    alt = selection;
                }
                cm.replaceSelection("![" + alt + "](" + link + ")");

                var cursorPos = cm.getCursor();
                if (!selection) {
                    cm.setCursor(cursorPos.line, cursorPos.ch - 3);
                } else if (link) {
                    cm.setCursor(cursorPos.line, cursorPos.ch - (3 + link.length));
                } else {
                    cm.setCursor(cursorPos.line, cursorPos.ch - 1);
                }
            }
        },
        {
            label: "<span class=\"glyphicon glyphicon-question-sign\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + CodeMirrorLabels.help + "\"></span>",
            callback: function (cm) {
                window.location.href = "https://guides.github.com/features/mastering-markdown/";
            }
        }
    ]
});