<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\HttpException;

class ApiController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => false,
                        'roles' => ['?'],
                        'denyCallback' => function($rule, $action) {
                            return $this->redirect(Url::toRoute(['/site/login']));
                        }
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            /** @var User $user */
                            $user = Yii::$app->user->getIdentity();
                            return $user->isAdmin();
                        }
                    ],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
        if (!Yii::$app->user->getIdentity()->isAdmin()){
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }
        return $this->render('index', [
            'data' => ['data1' => 1, 'data2' => 2, 'data3' => 3],
        ]);
    }

}
