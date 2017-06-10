<?php

namespace app\modules\controllers;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Product;
use app\models\User;
use app\models\Address;
use yii\web\Controller;
use yii\data\Pagination;
use Yii;
use app\modules\controllers\CommonController;

class OrderController extends CommonController
{
    public function actionList()
    {
        $this->layout = "layout1";
        //获取Order表的模型
        $model = Order::find();
        // 获取模型中所有行数
        $count = $model->count();
        // 定义order的取出每页行数
        $pageSize = Yii::$app->params['pageSize']['order'];
        // 定出多少页
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        // 限定每页数据偏移
        $data = $model->offset($pager->offset)->limit($pager->limit)->all();
        // 
        $data = Order::getDetail($data);
        return $this->render('list', ['pager' => $pager, 'orders' => $data]);
    }

    public function actionDetail()
    {+
        $this->layout = "layout1";
        $orderid = (int)Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        $data = Order::getData($order);
        return $this->render('detail', ['order' => $data]);
    }

    public function actionSend()
    {
        $this->layout = "layout1";
        $orderid = (int)Yii::$app->request->get('orderid');
        $model = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        $model->scenario = "send";
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->status = Order::SENDED;
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '发货成功');
            }
        }
        return $this->render('send', ['model' => $model]);
    }

}
