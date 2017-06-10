<?php

namespace app\modules\controllers;
use app\models\Back;
// use app\models\Category;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use crazyfd\qiniu\Qiniu;
use app\modules\controllers\CommonController;
/**
 *Public controller for the `admin` module
 */
class BackController extends CommonController
{
    public function actionAdd()
    {
        $this->layout='main';
        $model = new Back;
        var_dump($model);die;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
        }
        return $this->render("add", ['model' => $model]);
    }
    public function actionList()
    {
        $model = Back::find();
        // var_dump($model);
        $count = $model->count();
        // var_dump($count);die;
        $pageSize = Yii::$app->params['pageSize']['back'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $products = $model->offset($pager->offset)->limit($pager->limit)->all();
        // var_dump($products);die;
        $this->layout = "main";
        return $this->render("back", ['pager' => $pager, 'products' => $products]);
    }
    public function actionMod()
    {
        $this ->layout='main';
        $cate = new Category;
        $list = $cate->getOptions();
        unset($list[0]);

        $productid = Yii::$app->request->get("productid");
        $model = Product::find()->where('productid = :id', [':id' => $productid])->one();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
            $post['Product']['cover'] = $model->cover;
            if ($_FILES['Product']['error']['cover'] == 0) {
                $key = uniqid();
                $qiniu->uploadFile($_FILES['Product']['tmp_name']['cover'], $key);
                $post['Product']['cover'] = $qiniu->getLink($key);
                $qiniu->delete(basename($model->cover));
            }
            $pics = [];
            foreach($_FILES['Product']['tmp_name']['pics'] as $k => $file) {
                if ($_FILES['Product']['error']['pics'][$k] > 0) {
                    continue;
                }
                $key = uniqid();
                $qiniu->uploadfile($file, $key);
                $pics[$key] = $qiniu->getlink($key);
            }
            $post['Product']['pics'] = json_encode(array_merge((array)json_decode($model->pics, true), $pics));
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
        }
        return $this->render('add', ['model' => $model, 'opts' => $list]);
    }
    public function actionOn() 
    {
        $productid = Yii::$app->request->get("productid");
        Product::updateAll(['ison' => '1'],'productid =:pid',[':pid' => $productid]);
        return $this->redirect(['product/list']);
    }
    public function actionOff() 
    {
        $productid = Yii::$app->request->get("productid");
        Product::updateAll(['ison' => '0'],'productid =:pid',[':pid' => $productid]);
        return $this->redirect(['product/list']);
    }
}
