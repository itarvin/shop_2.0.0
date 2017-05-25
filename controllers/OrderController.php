<?php
namespace app\controllers;
use app\controllers\CommonController;
use Yii;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Cart;
use app\models\Product;
use app\models\User;
use app\models\Address;
use app\models\Pay;
use dzer\express\Express;

class OrderController extends CommonController
{
    public function actionIndex()
    {
        $this->layout = "layout2";
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $orders = Order::getProducts($userid);
        return $this->render("index", ['orders' => $orders]);
    }

    public function actionCheck()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $orderid = Yii::$app->request->get('orderid');
        $status = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one()->status;
        if ($status != Order::CREATEORDER && $status != Order::CHECKORDER) {
            return $this->redirect(['order/index']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $addresses = Address::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $orderid])->asArray()->all();
        $data = [];
        foreach($details as $detail) {
            $model = Product::find()->where('productid = :pid' , [':pid' => $detail['productid']])->one();
            $detail['title'] = $model->title;
            $detail['cover'] = $model->cover;
            $data[] = $detail;
        }
        $express = Yii::$app->params['express'];
        $expressPrice = Yii::$app->params['expressPrice'];
        $this->layout = "layout1";
        return $this->render("check", ['express' => $express, 'expressPrice' => $expressPrice, 'addresses' => $addresses, 'products' => $data]);
    }

    public function actionAdd()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                // var_dump($post);die;
                // array(3) { ["_csrf"]=> string(56) "aWR4RkltYi5QXDQHLzcbeFEAOzMbDBNpPAIwDg0jKmEHADorZF0RFw==" ["OrderDetail"]=> array(1) { [0]=> array(3) { ["productid"]=> string(1) "6" ["price"]=> string(6) "236.00" ["productnum"]=> string(1) "2" } } ["productnum"]=> string(1) "2" } 
                $ordermodel = new Order;
                $ordermodel->scenario = 'add';
                // var_dump($ordermodel);die;
                // object(app\models\Order)#96 (12) { ["products"]=> NULL ["zhstatus"]=> NULL ["username"]=> NULL ["address"]=> NULL ["_attributes":"yii\db\BaseActiveRecord":private]=> array(0) { } ["_oldAttributes":"yii\db\BaseActiveRecord":private]=> NULL ["_related":"yii\db\BaseActiveRecord":private]=> array(0) { } ["_errors":"yii\base\Model":private]=> NULL ["_validators":"yii\base\Model":private]=> NULL ["_scenario":"yii\base\Model":private]=> string(3) "add" ["_events":"yii\base\Component":private]=> array(0) { } ["_behaviors":"yii\base\Component":private]=> array(0) { } } 
                $usermodel = User::find()->where('username = :name or useremail = :email', [':name' => Yii::$app->session['loginname'], ':email' => Yii::$app->session['loginname']])->one();
                // var_dump($usermodel);die;
                //object(app\models\User)#109 (11) { ["repass"]=> NULL ["loginname"]=> NULL ["rememberMe"]=> bool(true) ["_attributes":"yii\db\BaseActiveRecord":private]=> array(5) { ["userid"]=> string(1) "2" ["username"]=> string(5) "arvin" ["userpass"]=> string(32) "40824539d8c9938ae241fad6372bec7c" ["useremail"]=> string(17) "1182850217@qq.com" ["createtime"]=> string(10) "1495528828" } ["_oldAttributes":"yii\db\BaseActiveRecord":private]=> array(5) { ["userid"]=> string(1) "2" ["username"]=> string(5) "arvin" ["userpass"]=> string(32) "40824539d8c9938ae241fad6372bec7c" ["useremail"]=> string(17) "1182850217@qq.com" ["createtime"]=> string(10) "1495528828" } ["_related":"yii\db\BaseActiveRecord":private]=> array(0) { } ["_errors":"yii\base\Model":private]=> NULL ["_validators":"yii\base\Model":private]=> NULL ["_scenario":"yii\base\Model":private]=> string(7) "default" ["_events":"yii\base\Component":private]=> array(0) { } ["_behaviors":"yii\base\Component":private]=> array(0) { } } 
                if (!$usermodel) {
                    throw new \Exception();
                }
                $userid = $usermodel->userid;
                $ordermodel->userid = $userid;
                // var_dump($userid);die;
                // string(1) "2" 
                $ordermodel->status = Order::CREATEORDER;
                // int(0) 
                // var_dump($ordermodel->status);die;
                $ordermodel->createtime = time();
                if (!$ordermodel->save()) {
                    throw new \Exception();
                }
                $orderid = $ordermodel->getPrimaryKey();
                // var_dump($orderid);die;
                // string(2) "27"
                // var_dump($post['OrderDetail']);die;
                foreach ($post['OrderDetail'] as $product) {
                    $model = new OrderDetail;
                    $product['orderid'] = $orderid;
                    $product['createtime'] = time();
                    $data['OrderDetail'] = $product;
                    if (!$model->add($data)) {
                        throw new \Exception();
                    }
                    Cart::deleteAll('productid = :pid' , [':pid' => $product['productid']]);
                    Product::updateAllCounters(['num' => -$product['productnum']], 'productid = :pid', [':pid' => $product['productid']]);
                }
            }
            // var_dump($orderid);die;
            $transaction->commit();
        }catch(\Exception $e) {
            $transaction->rollback();
            return $this->redirect(['cart/index']);
        }
        return $this->redirect(['order/check', 'orderid' => $orderid]);
    }

    public function actionConfirm()
    {
        //addressid, expressid, status, amount(orderid,userid)
        try {
            if (Yii::$app->session['isLogin'] != 1) {
                return $this->redirect(['member/auth']);
            }
            if (!Yii::$app->request->isPost) {
                throw new \Exception();
            }
            $post = Yii::$app->request->post();
            $loginname = Yii::$app->session['loginname'];
            $usermodel = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one();
            if (empty($usermodel)) {
                throw new \Exception();
            }
            $userid = $usermodel->userid;
            $model = Order::find()->where('orderid = :oid and userid = :uid', [':oid' => $post['orderid'], ':uid' => $userid])->one();
            if (empty($model)) {
                throw new \Exception();
            }
            $model->scenario = "update";
            $post['status'] = Order::CHECKORDER;
            $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $post['orderid']])->all();
            $amount = 0;
            foreach($details as $detail) {
                $amount += $detail->productnum*$detail->price;
            }
            if ($amount <= 0) {
                throw new \Exception();
            }
            $express = Yii::$app->params['expressPrice'][$post['expressid']];
            if ($express < 0) {
                throw new \Exception();
            }
            $amount += $express;
            $post['amount'] = $amount;
            $data['Order'] = $post;
			if (empty($post['addressid'])) {
				return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
			}
            if ($model->load($data) && $model->save()) {
                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
            }
        }catch(\Exception $e) {
            return $this->redirect(['index/index']);
        }
    }

    public function actionPay()
    {
        try{
            if (Yii::$app->session['isLogin'] != 1) {
                throw new \Exception();
            }
            $orderid = Yii::$app->request->get('orderid');
            $paymethod = Yii::$app->request->get('paymethod');
            if (empty($orderid) || empty($paymethod)) {
                throw new \Exception();
            }
            if ($paymethod == 'alipay') {
                return Pay::alipay($orderid);
            }
        }catch(\Exception $e) {}
        return $this->redirect(['order/index']);
    }

    public function actionGetexpress()
    {
        $expressno = Yii::$app->request->get('expressno');
        $res = Express::search($expressno);
        echo $res;
        exit;
    }

    public function actionReceived()
    {
        $orderid = Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        if (!empty($order) && $order->status == Order::SENDED) {
            $order->status = Order::RECEIVED;
            $order->save();
        }
        return $this->redirect(['order/index']);
    }

}
