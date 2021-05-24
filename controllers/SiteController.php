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
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
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
                $key = md5(rand(1000, 9999));
                $user->generatePasswordResetToken($key);
                $user->save();
                $urlData = 'http://localhost:8080/site/set-password?passreset='
                    . base64_encode(json_encode([
                        'email' => $user->email,
                        'access_token' => $key,
                    ]));
                $emailData = [
                    'email' => $user->email,
                    'title' => 'Сброс пароля',
                    'data' => $urlData,
                ];
                SendMail::sendEmail($emailData);
                Yii::$app->session->setFlash(
                    'success',
                    'Данные для восстановления пароля отправлены на email'
                );
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
                if ($model->updatePassword()) {
                    Yii::$app->session->setFlash(
                        'success',
                        'Пароль отправлен на email'
                    );
                    return $this->goHome();
                }
                Yii::$app->session->setFlash(
                    'danger',
                    'Ошибка'
                );
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
}
