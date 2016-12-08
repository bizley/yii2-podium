<?php

namespace bizley\podium\widgets\codemirror;

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
        'bizley\podium\widgets\codemirror\CodeMirrorLibAsset',
        'bizley\podium\widgets\codemirror\CodeMirrorExtraAsset',
        'bizley\podium\widgets\codemirror\CodeMirrorModesAsset',
        'bizley\podium\widgets\codemirror\CodeMirrorButtonsAsset'
    ];
}
