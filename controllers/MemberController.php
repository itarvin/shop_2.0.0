<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;

class MemberController extends Controller
{
    
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionAuth()
    {
        $this ->layout = "layouts2";
        return $this->render('auth');
    }
    public function actionReg()
    {
        $model = new User;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if ($model ->regByMail($post)) {
                Yii::$app ->session->setFlash('info', '电子邮件发送成功！');
            }
        }
        $this ->layout ='layouts2';
        return $thisrender('auth',['model' => $model]);
    }
}
