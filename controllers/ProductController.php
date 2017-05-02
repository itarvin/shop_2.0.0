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
    public $layout = false;
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
      // echo '123456';
        // $this->layout=false;
        return $this->render('index');
    }
    public function actionDetail()
    {
        // $this->layout=false;
        return $this -> render('detail');
    }
}
