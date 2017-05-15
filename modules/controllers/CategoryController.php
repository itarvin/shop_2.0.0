<?php

namespace app\modules\controllers;
use app\models\Category;
use yii\web\Controller;
use Yii;

/**
 *Public controller for the `admin` module
 */
class CategoryController extends Controller
{
    public function actionLst()
    {
        $this->layout="main";
        $model = new Category;
        $cates = $model->getTreeList();
        return $this->render("lst",['cates' =>$cates]);
    }
    public function actionAdd()
    {
        $model = new Category();
        // $list[0] = "添加顶级分类";
        $list = $model ->getOptions();
        $this->layout="main";
        if (Yii::$app->request->isPost) {
            $post = Yii::$app ->request->post();
            if ($model->add($post))
                Yii::$app->session->setFlash('info','添加分类成功！');
        }
        return $this->render("add",['list' => $list,'model' =>$model]);
    }
    public function actionMod()
    {
        $this->layout = "main";
        $cateid = Yii::$app->request->get("cateid");
        $model = Category::find()->where('cateid = :id', [':id' => $cateid])->one();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
        }
        $list = $model->getOptions();
        return $this->render('add', ['model' => $model, 'list' => $list]);
    }
    public function actionDel(){
        try {
            $cateid = Yii::$app ->request->get("cateid");
            if (empty($cateid)) {
                throw new \Exception('参数错误');
            }
            $data = Category::find()->where('parentid = :pid',[":pid" => $cateid])->one();
            if ($data) {
                throw new \Exception('该分类下有子分类！不允删除');
            }
            if (!Category::deleteAll('cateid = :id',[':id' => $cateid])) {
                throw new \Exception('删除分类数据失败！');
            }
        }catch(\Exception $e){
            Yii::$app->sessio->setFlash('info','删除分类数据失败！');
        }
        return $this ->redirect(['category/lst']);
    }
}
