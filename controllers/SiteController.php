<?php

namespace app\controllers;

use app\models\SendMail;
use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @return array[]
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionResetPassword()
    {
        $model = new User();
        if ($model->load(Yii::$app->request->post())) {
            $user = User::findByEmail($model->email) ?: null;
            if (!empty($user)) {
                $user->access_token = md5(rand(1000, 9999));
                $user->generatePasswordResetToken($user->access_token);
                $user->save();
                $urlData = 'http://localhost:8080/site/set-password?passreset='
                    . base64_encode(json_encode([
                        'email' => $user->email,
                        'access_token' => $user->access_token,
                    ]));
                $emailData = [
                    'email' => $user->email,
                    'title' => 'Сброс пароля',
                    'data' => $urlData,
                ];
                SendMail::sendEmail($emailData);

                return $this->goHome();
            }
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * @return Response
     */
    public function actionSetPassword()
    {
        if (Yii::$app->request->get()['passreset']) {
            $result = json_decode(base64_decode(Yii::$app->request->get()['passreset']),
                true
            );
            $model = User::findByEmail($result['email']) ?: null;
            if ($model->validateToken($result['access_token'])) {
                $model->updatePassword();

                return $this->goHome();
            }
        }
    }

    /**
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * @return string|Response
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(
                Yii::$app->request->post())
            && $model->contact(Yii::$app->params['adminEmail'])
        ) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
