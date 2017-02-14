<?php

namespace bizley\podium\widgets\codemirror\assets;

use yii\web\AssetBundle;

/**
 * CodeMirror Assets
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class CodeMirrorAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        'bizley\podium\widgets\codemirror\assets\CodeMirrorLibAsset',
        'bizley\podium\widgets\codemirror\assets\CodeMirrorExtraAsset',
        'bizley\podium\widgets\codemirror\assets\CodeMirrorModesAsset',
        'bizley\podium\widgets\codemirror\assets\CodeMirrorButtonsAsset',
        'bizley\podium\widgets\codemirror\assets\CodeMirrorConfigAsset'
    ];
}
