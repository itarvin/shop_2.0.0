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
    public $layout = false;
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionAuth()
    {
        return $this->render('auth');
    }
}
