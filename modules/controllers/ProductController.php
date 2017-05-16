<?php

namespace app\modules\controllers;
use app\models\Product;
use app\models\Product;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;

/**
 *Public controller for the `admin` module
 */
class ProductController extends Controller
{
    public function actionAdd()
    {
        $this->layout="main";
        $model = new Product;
        $cate = new Category;
        $list = $cate ->getOptions();
        unset($list[0]);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $pics = $this ->upload();
            if ($!$pics) {
                $model ->addError('cover','封面不能为空！');
            }
        }
        return $this->render("add",['model' =>$model]);
    }
    public function actionList()
    {
        $this->layout="main";
        $model = Product::find();
        $count = $model ->count();
        $pageSize = Yii::$app->params['pageSize']['Product'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $products = $model ->offset($pager->offset)->limit()->all();
        return $this->render("products",['model' =>$model]);
    }
    private function upload()
    {
        if ($_FILES['Product']['error']['cover'] > 0) {
            return false;
        }
    }
}
