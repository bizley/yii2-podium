<?php

namespace bizley\podium\widgets;

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
     * Set true to get theme default buttons.
     * You can use above constants for predefined set of buttons.
     * For other options see README and https://quilljs.com/docs/modules/toolbar/
     * @since 2.0
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
     * This property is skipped if $configuration is set.
     * Notice: if you set 'toolbar' module it will replace $toolbarOptions configuration.
     * @since 2.0
     */
    public $modules = ['syntax' => true];
    
    /**
     * @var string Highlight.js stylesheet to fetch from https://cdnjs.cloudflare.com
     * See https://github.com/isagalaev/highlight.js/tree/master/src/styles
     * Used when Syntax module is added.
     * @since 2.0
     */
    public $highlightStyle = 'github-gist.min.css';
    
    /**
     * @var array HTML attributes for the input tag.
     * @see Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['style' => 'min-height:320px;'];
}
