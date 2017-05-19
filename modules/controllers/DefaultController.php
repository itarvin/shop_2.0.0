<?php

namespace app\modules\controllers;
use app\modules\controllers\CommonController;
use yii\web\Controller;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends CommonController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
    	$this->layout="main";
    	// session_start();
    	// var_dump($_SESSION['admin']['adminuser']);die;
        return $this->render('index');
    }
}
