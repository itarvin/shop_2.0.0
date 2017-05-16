<?php
namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Product extends ActiveRecord
{
    public static function tableName()
    {
        return "{{%product}}";
    }

    public function rules()
    {
        return [
            ['loginname', 'required', 'message' => '登录用户名不能为空', 'on' => ['login']],
        
        ];
    }
    public function attributeLabels()
    {
        return [
            'username' => '用户名',
            'userpass' => '用户密码',
            'repass' => '确认密码',
            'useremail' => '电子邮箱',
            'loginname' => '用户名/电子邮箱',
        ];
    }
}
