<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Изменить пароль', [
            'class' => 'btn btn-success',
            //'title' => '123',
            'name' => 'button_cb'
        ]); ?>
    </div>
<!--
    <?= $form->field($model, 'access_token')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'password_reset_token')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'role')->textInput() ?>
    <?= $form->field($model, 'created_at')->textInput(['readonly' => true]) ?>
    <?= $form->field($model, 'updated_at')->textInput(['readonly' => true]) ?>
-->
    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
