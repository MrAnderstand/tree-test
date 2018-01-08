<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Реализации справочника ОКПД2';
?>

<div class="site-index">
	<div><?= Html::a('Nested sets (медленно)', '/nested-sets'); ?></div>
	<div><?= Html::a('Pid + redis (требует redis)', '/pid'); ?></div>
</div>
