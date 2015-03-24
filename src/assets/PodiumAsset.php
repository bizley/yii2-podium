<?php

namespace bizley\podium\assets;

use yii\web\AssetBundle;

class PodiumAsset extends AssetBundle
{
    public $sourcePath = '@podium/css';
    public $css = [
        'podium.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    
}
