<?php

namespace app\modules\controllers;
use app\models\User;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use app\modules\controllers\CommonController;

class UserController extends CommonController
{
    public function actionUsers()
    {
        $model = User::find()->joinWith('profile');
        $count = $model->count();
        // var_dump($count);die;
        $pageSize = Yii::$app->params['pageSize']['user'];
        // var_dump($pageSize);die;
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $users = $model->offset($pager->offset)->limit($pager->limit)->all();
        $this->layout="main";
        return $this->render('users', ['users' => $users, 'pager' => $pager]);
    }
    public function actionReg()
    {
        $this ->layout = "main";
        $model = new User; 
        if (Yii::$app->request->isPost)
        {
            $post = Yii::$app->request->post();
            // var_dump($post);die;
            if ($model ->reg($post)) {
                Yii::$app->session->setFlash('info','数据添加成功！');
            }
            else{
                Yii::$app->session->setFlash('info','数据添加失败！');
            }
        }
        $model ->userpass = '';
        $model ->repass = '';
        return $this->render('reg',['model' => $model]);
    }
    public function actionDel()
    {
        $userid = (int)Yii::$app->request->get("userid");
        if (empty($userid))
        {
            throw new \Exception();
            // $this->redirect(['user/users']);
        }
        $trans = Yii::$app ->db->beginTransaction();
        if ($obj = Profile::find()->where('userid = :id', [':is' =>$userid])->one())
        {
            $res = Profile::deleteAll('userid =:id',[':id' => $userid]);
            if (empty($res)) {
                throw new \Exception();
            }
        }
        if (!User::delateAll('userid =:id',[':id' => $userid])) {
            throw new \Exception();
        }
        $trans ->commit();
    }catch (\Exception $e) {
        if (Yii::$app->db->gettransaction()) {
            $trans ->rollback();
        }
        $this ->redirect(['user/users']);
    }
    public function actionChangepass()
    {
        $this ->layout="main";
        $model = User::find()->where('adminuser = :user', [':user' => Yii::$app ->session['admin']['adminuser']]) ->one();
        if (Yii::$app->request->isPost)
        {
            $post =Yii::$app->request->post();
            if ($model ->changepass($post))
            {
                Yii::$app->session->setFlash('info','密码修改成功！');
            }
        }
        // var_dump($_SESSION['admin']['adminuser']);die;
        $model->adminpass ='';
        $model->repass =''; 
        return $this->render('changepass',['model' => $model]);
    }
}
