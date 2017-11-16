<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Админка';
?>
<div class="site-index">
    <div class="form-group">
        <?= Html::a('Скачать валидный справочник', ['/files/OKPD2.xlsx'], ['class' => 'btn btn-success']) ?>
    </div>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    
    <?= $form->field($model, 'file')->fileInput(); ?>

    <div class="form-group">
        <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary', 'data' => [
                'confirm' => 'При попытке загрузить другую версию справочника ОКПД2 существующая будет удалена. Продолжить?',
                'method' => 'post',
            ],]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
