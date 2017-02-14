CodeMirror.findModeByName("php").mime = "text/x-php";
var CmWrap = function (cm, chars) {
    var selection = cm.getSelection();
    cm.replaceSelection(chars + selection + chars);
    if (!selection) {
        var cursorPos = cm.getCursor();
        cm.setCursor(cursorPos.line, cursorPos.ch - chars.length);
    }
};
var CmPrefix = function (cm, char) {
    cm.replaceSelection(char + " " + cm.getSelection());
};
var CmWrapTwo = function (cm, char) {
    var selection = cm.getSelection();
    var text = "";
    var link = "";
    if (selection.match(/^https?:\/\//)) {
        link = selection;
    } else {
        text = selection;
    }
    cm.replaceSelection(char + text + "](" + link + ")");
    var cursorPos = cm.getCursor();
    if (!selection) {
        cm.setCursor(cursorPos.line, cursorPos.ch - 3);
    } else if (link) {
        cm.setCursor(cursorPos.line, cursorPos.ch - (3 + link.length));
    } else {
        cm.setCursor(cursorPos.line, cursorPos.ch - 1);
    }
};
var basicButtons = [
    {
        hotkey: "Ctrl-B",
        icon: "bold",
        title: CodeMirrorLabels.bold,
        callback: function (cm) {
            CmWrap(cm, "**");
        }
    },
    {
        hotkey: "Ctrl-I",
        icon: "italic",
        title: CodeMirrorLabels.italic,
        callback: function (cm) {
            CmWrap(cm, "*");
        }
    },
    {
        icon: "list",
        title: CodeMirrorLabels.bulletedlist,
        callback: function (cm) {
            CmPrefix(cm, "-");
        }
    },
    {
        icon: "list-alt",
        title: CodeMirrorLabels.orderedlist,
        callback: function (cm) {
            CmPrefix(cm, "1.");
        }
    },
    {
        icon: "link",
        title: CodeMirrorLabels.link,
        callback: function (cm) {
            CmWrapTwo(cm, "[")
        }
    }
];
var fullButtons = [
    {
        icon: "option-horizontal",
        title: CodeMirrorLabels.inlinecode,
        callback: function (cm) {
            CmWrap(cm, "`");
        }
    },
    {
        icon: "option-vertical",
        title: CodeMirrorLabels.blockcode,
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
        icon: "header",
        title: CodeMirrorLabels.header,
        callback: function (cm) {
            CmPrefix(cm, "#");
        }
    },
    {
        icon: "console",
        title: CodeMirrorLabels.quote,
        callback: function (cm) {
            CmPrefix(cm, ">");
        }
    },
    {
        icon: "picture",
        title: CodeMirrorLabels.image,
        callback: function (cm) {
            CmWrapTwo(cm, "![")
        }
    }
];
var CodeMirrorButtons = [];
var addButtons = function (arr, buttons) {
    for (var i in buttons) {
        var button = {};
        if (buttons[i].hotkey) {
            button.hotkey = buttons[i].hotkey;
        }
        button.label = "<span class=\"glyphicon glyphicon-" + buttons[i].icon + "\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" + buttons[i].title + "\"></span></div>";
        button.callback = buttons[i].callback;
        arr.push(button);
    }
    return arr;
};
addButtons(CodeMirrorButtons, basicButtons);
if (CodeMirrorSet === 'full') {
    addButtons(CodeMirrorButtons, fullButtons);
}
addButtons(CodeMirrorButtons, [{icon: "question-sign", title: CodeMirrorLabels.help, callback: function (cm) {
        window.location.href = "https://guides.github.com/features/mastering-markdown/";
    }}]);
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
    buttons: CodeMirrorButtons
});
