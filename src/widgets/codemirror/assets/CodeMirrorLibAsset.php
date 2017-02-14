<?php

namespace bizley\podium\widgets\codemirror\assets;

use yii\web\AssetBundle;

/**
 * CodeMirror Library Assets
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class CodeMirrorLibAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/codemirror/lib';

    /**
     * @inheritdoc
     */
    public $css = ['codemirror.css'];

    /**
     * @inheritdoc
     */
    public $js = ['codemirror.js'];
}
