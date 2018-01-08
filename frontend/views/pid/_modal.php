<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

?>

<?php Modal::begin([
    'id' => 'nodeModal',
    'header' => '<h2>Заполните поля</h2>',
]); ?>

<?php Pjax::begin(['id' => 'nodeManipulation', 'enablePushState' => false, 'timeout' => 5000]); ?>
<?php $form = ActiveForm::begin(['id' => 'nodeManipulationForm', 'ajaxDataType' => 'json', 'options' => ['data-pjax' => true]]); ?>
    <?= $form->field($model, 'code')->textInput(); ?>
    <?= $form->field($model, 'name')->textInput(); ?>
    <?= Html::hiddenInput('id', $id); ?>
    <?= Html::hiddenInput('preload'); ?>
<div>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary save']); ?>
    <?= Html::button('Отмена', ['class' => 'btn btn-default cancel']); ?>
</div>
<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>

<?php Modal::end(); ?>