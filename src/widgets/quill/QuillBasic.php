<?php

namespace bizley\podium\widgets\quill;

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
     */
    public $toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        [['list' => 'ordered'], ['list' => 'bullet']],
        [['align' => []]],
        ['link']
    ];
}
