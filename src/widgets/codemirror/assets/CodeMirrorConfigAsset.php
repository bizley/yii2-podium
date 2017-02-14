<?php

namespace bizley\podium\widgets\codemirror\assets;

use yii\web\AssetBundle;

/**
 * CodeMirror config Assets
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class CodeMirrorConfigAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@podium/widgets/codemirror/podium';

    /**
     * @inheritdoc
     */
    public $js = ['podium-codemirror.js'];

    /**
     * @inheritdoc
     */
    public $css = ['podium-codemirror.css'];
}
