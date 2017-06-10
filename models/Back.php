<?php
namespace app\models;
use yii\db\ActiveRecord;

class Back extends ActiveRecord
{
    public $cate;
// 提交的字段定义规则
    public function rules()
    {
        return [
            ['xuehao', 'required', 'message' => '学号不能为空'],
            ['name', 'required', 'message' => '姓名不能为空'],
            ['back', 'required', 'message' => '银行卡不能为空'],
            ['sfid', 'required', 'message' => '身份证号码不能为空'],
            ['学费', 'required', 'message' => '学费不能为空'],
        ];
    }
// 给字段赋予新属性
    public function attributeLabels()
    {
        return [
            'xuehao'  => '学号',
            'name'  => '姓名',
            'back'  => '银行卡号码',
            'sfid'  => '是否热卖',
            'numbers' => '应交学费总额',
        ];
    }
// 要使用的数据表
    public static function tableName()
    {
        return "{{%product}}";
    }
// 添加方法
    public function add($data)
    {
        if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }
}
