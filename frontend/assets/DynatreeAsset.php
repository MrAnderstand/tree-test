<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class DynatreeAsset extends AssetBundle
{
    public $sourcePath = '@bower/dynatree/dist';
    public $css = [
        'skin-vista/ui.dynatree.css',
    ];
    public $js = [
        'jquery.dynatree.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}
