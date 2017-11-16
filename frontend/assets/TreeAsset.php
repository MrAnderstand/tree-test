<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class TreeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/tree.js',
    ];
    public $depends = [
        'frontend\assets\DynatreeAsset',
    ];
}
