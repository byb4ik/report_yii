<?php

namespace app\controllers;

class APIController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
