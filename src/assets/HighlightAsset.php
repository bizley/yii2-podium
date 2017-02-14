<?php

namespace bizley\podium\assets;

use yii\web\AssetBundle;

/**
 * Highlight.js CSS asset.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class HighlightAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $css = ['https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.8.0/styles/github-gist.min.css'];
}
