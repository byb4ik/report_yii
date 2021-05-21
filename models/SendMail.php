<?php


namespace app\models;

use Yii;

class SendMail
{
    public static function sendEmail($data){
        return Yii::$app->mailer->compose()
            ->setFrom('vladevranov@gmail.com')
            ->setTo($data['email'])
            ->setSubject($data['title'])
            ->setHtmlBody('Уважаемый(ая) : <b>' . $data['name'] . '</b><br>' .
                'Ваш новый пароль: <b>' .  $data['data'] . '</b>'
            )
            ->send();
    }
}