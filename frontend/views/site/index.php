<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Тестовое задание ОКПД2';

$js = <<<EOT
var treeData = $treeData;
var treeSearchUrl = '$treeSearchUrl';
var addSectionUrl = '$addSectionUrl';
var addNodeUrl = '$addNodeUrl';
var editNodeUrl = '$editNodeUrl';
var deleteNodeUrl = '$deleteNodeUrl';
EOT;
$this->registerJs($js, yii\web\View::POS_HEAD);
?>

<div class="site-index">
    <div class="btn-group">
        <?= Html::button('Добавить раздел', ['id' => 'addSection', 'class' => 'btn btn-success']); ?>
        <?= Html::button('Добавить элемент', ['id' => 'addNode', 'class' => 'btn btn-success']); ?>
        <?= Html::button('Изменить', ['id' => 'editNode', 'class' => 'btn btn-default', 'disabled' => 'disabled']); ?>
        <?= Html::button('Удалить', ['id' => 'deleteNode', 'class' => 'btn btn-danger', 'disabled' => 'disabled']); ?>
    </div>
    <?= Html::input('text', 'search', null, ['id' => 'search', 'class' => 'form-control', 'placeholder' => 'Поиск. Введите не менее 3х символов.']); ?>
    <div id="tree"></div>
</div>

<?= $modal ?>