<?php

namespace app\controllers;
use app\controllers\CommonController;

use Yii;
use app\models\User;
use app\models\Address;
    /*
    * Adress Controller 
    *
    */
class AddressController extends CommonController
{
    /*
    * add action
    *
    */
    public function actionAdd()
    {
        //判断用户是否登录
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        //存储session中的用户名
        $loginname = Yii::$app->session['loginname'];
        //通过登陆的用户名或邮箱找登陆的用户id
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        //用户地址添加
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            //给post数组添加字段值
            $post['userid'] = $userid;
            $post['address'] = $post['address1'].$post['address2'];
            $data['Address'] = $post;
            //实例化 and 加载 and 存储
            $model = new Address;
            $model->load($data);
            $model->save();
        }
        //$_SERVER['HTTP_REFERER'] 返回获取前一页面的 URL 地址
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function actionDel()
    {
        if (Yii::$app->session['isLogin'] != 1) {
            return $this->redirect(['member/auth']);
        }
        $loginname = Yii::$app->session['loginname'];
        $userid = User::find()->where('username = :name or useremail = :email', [':name' => $loginname, ':email' => $loginname])->one()->userid;
        $addressid = Yii::$app->request->get('addressid');
        if (!Address::find()->where('userid = :uid and addressid = :aid', [':uid' => $userid, ':aid' => $addressid])->one()) {
            return $this->redirect($_SERVER['HTTP_REFERER']);
        }
        Address::deleteAll('addressid = :aid', [':aid' => $addressid]);
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}
