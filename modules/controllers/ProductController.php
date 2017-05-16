<?php

namespace app\modules\controllers;
use app\models\Product;
use app\models\Category;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use crazyfd\qiniu\Qiniu;

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
        $list = $cate->getOptions();
        // var_dump($list);die;
        unset($list[0]);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $pics = $this->upload();
            if (!$pics) {
                $model->addError('cover', '封面不能为空');
            } else {
                $post['Product']['cover'] = $pics['cover'];
                $post['Product']['pics'] = $pics['pics'];
            }
            if ($pics && $model->add($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            } else {
                Yii::$app->session->setFlash('info', '添加失败');
            }

        }

        return $this->render("add", ['opts' => $list, 'model' => $model]);
    }
    public function actionList()
    {
        $model = Product::find();
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['product'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $products = $model->offset($pager->offset)->limit($pager->limit)->all();
        $this->layout = "main";
        return $this->render("products", ['pager' => $pager, 'products' => $products]);
    }
    private function upload()
    {
        if ($_FILES['Product']['error']['cover'] > 0) {
            return false;
        }
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAINm, Product::BUCKET);
        $key = uniqid();
        $qiniu ->uploadFile($_FILES['Product']['tmp_name']['cover'], $key);
        $cover = $qiniu->getLink($key);
        $pics = [];
        foreach ($_FILES['Product']['tmp_name']['pics'] as $k => $file) {
            if ($_FILES['Product']['error']['pics'][$k] >0) {
                continue;
            }
            $key = uniqid();
            $qiniu ->uploadFile($file,$key);
            $pics[$key] = $qiniu->getLink($key);
        }
        return ['cover' => $cover, 'pics' => json_encode($pics)];
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
    public function actionRemovepic()
    {
        $key = Yii::$app ->request->get("productid");
        $model = Product::find()->where('product =:pid', [':pid' => $productid]);
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $qiniu->delete($key);
        $pics = json_decode($model->pics,true);
        unset($pics[$key]);
        Product::updateAll(['pics' => json_encode($pics)],'productid =:pid',[':pid' => $productid]);
        return $this->redirect(['product/mod','productid' => $productid]);
    }
    public function actionDel() {
        $productid = Yii::$app->request->get("productid");
        $model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
        $key = basename($model->cover);
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $qiniu->delete($key);
        $pics = json_decode($model->pics, true);
        foreach($pics as $key=>$file) {
            $qiniu->delete($key);
        }
        Product::deleteAll('productid = :pid', [':pid' => $productid]);
        return $this->redirect(['product/list']);
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
