<?php

namespace bizley\podium\widgets;

use bizley\quill\Quill;

/**
 * Podium Quill widget with basic toolbar.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class QuillBasic extends Quill
{
    /**
     * @var bool|string|array Toolbar buttons.
     * Set true to get theme default buttons.
     * You can use above constants for predefined set of buttons.
     * For other options see README and https://quilljs.com/docs/modules/toolbar/
     * @since 2.0
     */
    public $toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'], 
        [['list' => 'ordered'], ['list' => 'bullet']], 
        [['align' => []]], 
        ['link']
    ];
    
    /**
     * @var array HTML attributes for the input tag.
     * @see Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['style' => 'min-height:150px;'];
}
