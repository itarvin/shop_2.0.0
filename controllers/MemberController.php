<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

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
}
