<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\assets;

use yii\web\AssetBundle;

/**
 * Podium Assets
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class PodiumAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@podium/css';
    
    /**
     * @inheritdoc
     */
    public $css = ['podium.css'];
    
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];    
}