<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class ProductController extends Controller
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
      // echo '123456';
        $this->layout="layouts2";
        return $this->render('index');
    }
    public function actionDetail()
    {
        $this->layout="layouts2";
        return $this -> render('detail');
    }
}
