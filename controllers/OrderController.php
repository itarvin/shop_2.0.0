<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class OrderController extends Controller
{
    // public $layout = false;
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionCheck()
    {
      // echo '123456';
        $this->layout="layouts2";
        return $this->render('check');
    }
     public function actionIndex()
    {
      // echo '123456';
        $this->layout="layouts2";
        return $this->render('index');
    }
}
