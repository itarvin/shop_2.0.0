<?php

namespace app\controllers;

use Yii;
use app\models\Product;
use yii\data\Pagination;
use app\controllers\CommonController;

class ProductController extends CommonController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
      // echo '123456';
        $this->layout="layouts";
        $cid = Yii::$app->request->get("cateid");
        $where = "cateid = :cid and ison = '1'";
        $params = [':cid' => $cid];
        $model = Product::find()->where($where, $params);
        $all = $model->asArray()->all();
        
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['frontproduct'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $all = $model->offset($pager->offset)->limit($pager->limit)->asArray()->all();

        $tui = $model->Where($where . ' and istui = \'1\'', $params)->orderby('createtime desc')->limit(5)->asArray()->all();
        $hot = $model->Where($where . ' and ishot = \'1\'', $params)->orderby('createtime desc')->limit(5)->asArray()->all();
        $sale = $model->Where($where . ' and issale = \'1\'', $params)->orderby('createtime desc')->limit(5)->asArray()->all();
        return $this->render("index", ['sale' => $sale, 'tui' => $tui, 'hot' => $hot, 'all' => $all, 'pager' => $pager, 'count' => $count]);
    }
    public function actionDetail()
    {
        $this->layout="layout2";
        $productid = Yii::$app->request->get("productid");
        $product = Product::find()->where('productid = :id', [':id' => $productid])->asArray()->one();
        $data['all'] = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(7)->all();
        return $this->render("detail", ['product' => $product, 'data' => $data]);
    }
}
