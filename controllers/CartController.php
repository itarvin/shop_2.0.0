<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Product;
use app\models\User;
use app\models\Cart;
class CartController extends Controller
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
    public function actionAdd()
    {
        if (Yii::$app->session['isLogin'])
            return $this->redirect(['member/auth']);
        $userid = User::find()->where('username =:name',[':name' => Yii::$app->session['loginname']])->one()->userid;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $num = Yii::$app->request->post()['productnum'];
            $data['Cart'] = $post;
            $data['Cart']['userid'] = $userid;
        }
        if (Yii::$app->request->isGet) {
            $productid = Yii::$app->request->get("productid = :pid",[':pid' => $productid])->one();
            $price = $model ->issale ? $model->saleprice : $model->price;
            $num = 1;
            $data['Cart'] = ['productid' => $productid, 'productnum' => $num, 'price' => $price,'userid' => $userid];
        }

    }
}
