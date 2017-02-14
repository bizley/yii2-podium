<?php

namespace bizley\podium\widgets\quill;

use bizley\quill\Quill;

/**
 * Podium Quill widget with full toolbar.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class QuillFull extends Quill
{
    /**
     * @var bool|string|array Toolbar buttons.
     */
    public $toolbarOptions = [
        [['align' => []], ['size' => ['small', false, 'large', 'huge']], 'bold', 'italic', 'underline', 'strike'],
        [['color' => []], ['background' => []]],
        [['header' => [1, 2, 3, 4, 5, 6, false]], ['script' => 'sub'], ['script' => 'super']],
        ['blockquote', 'code-block'],
        [['list' => 'ordered'], ['list' => 'bullet']],
        ['link', 'image', 'video'],
        ['clean']
    ];

    /**
     * @var array Collection of modules to include and respective options.
     */
    public $modules = ['syntax' => true];

    /**
     * @var string Highlight.js stylesheet to fetch from https://cdnjs.cloudflare.com
     */
    public $highlightStyle = 'github-gist.min.css';

    /**
     * @var string Additional JS code to be called with the editor.
     * @since 0.3
     */
    public $js = "{quill}.getModule('toolbar').addHandler('image',imageHandler);function imageHandler(){var range=this.quill.getSelection();var value=prompt('URL:');this.quill.insertEmbed(range.index,'image',value,Quill.sources.USER);};";
}
