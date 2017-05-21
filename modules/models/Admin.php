<?php

namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

/**
 * admin module definition class
 */
class Admin extends ActiveRecord
{
    public $rememberMe = true;
    public $repass;
    public static function tablesName()
    {
        return "{{%admin}}";
    }
    public function attributeLabels(){
        return [
           'adminuser' =>'管理员账号',
           'adminpass' =>'管理员密码',
           'adminemail' => '管理员邮箱',
           'repass' => '确认密码',
        ];
    }
    public function rules()
    {
    	return [
        	['adminuser', 'required','message'=>'管理员账号不能为空','on'=>['login','seekpass','changepass','adminadd','changeemail']],
        	['adminpass','required','message'=>'密码不能为空','on' => ['login','adminadd','changeemail','changepass']],
        	['rememberMe','boolean','on' => ['login','adminadd']],
        	['adminpass','validatePass','on' => ['login','changeemail']],
            ['adminemail','required','message'=>'电子邮箱不能为空','on'=> ['seekpass','adminadd','changeemail']],
            ['adminemail','email','message'=>'电子邮箱格式不正确！','on'=> ['seekpass','adminadd','changeemail']],
            ['adminemail','unique','message'=>'电子邮箱已经注册！','on'=> ['adminadd','changeemail']],
            ['adminuser','unique','message'=>'用户名已经注册！','on'=> ['adminadd']],
            ['adminemail','validateEmail','on'=> 'seekpass'],
            ['repass','required','message'=>'确认密码不能为空','on'=> ['changepass','adminadd']],
            ['repass', 'compare','compareAttribute' => 'adminpass', 'message' => '两次密码输入不一致', 'on' => ['changepass', 'adminadd']],
    	];
    }
    public function validatePass()
    {
    	if (!$this->hasErrors()) {
    		$data = self::find()->where('adminuser =:user and adminpass=:pass', [':user' =>$this->adminuser,':pass' =>md5(md5($this->adminpass))])->one();
    		if (is_null($data)) {
    			$this ->addError("adminpass","用户名或者密码错误！");
    		}
    	}
    }

    public function validateEmail()
    {
        if (!$this->hasErrors()) {
            $data = self::find()->where('adminuser = :user and adminemail =:email',[':user' => $this ->adminuser,':email' => $this ->adminemail])->one();
            if (is_null($data)) {
                $this ->addError("adminemail","管理员邮箱不匹配！");
            }
        }
    }

    public function login($data)
    {
    	  $this->scenario = "login";
        if ($this->load($data) && $this->validate()) {
            //做点有意义的事
            $lifetime = $this->rememberMe ? 24*3600 : 0;
            $session = Yii::$app->session;
            session_set_cookie_params($lifetime);
            $session['admin'] = [
                'adminuser' => $this->adminuser,
                'isLogin' => 1,
            ];
            $this->updateAll(['logintime' => time(), 'loginip' => ip2long(Yii::$app->request->userIP)], 'adminuser = :user', [':user' => $this->adminuser]);
            return (bool)$session['admin']['isLogin'];
        }
        return false;
    }

    public function seekPass($data)
    {
        $this->scenario = "seekpass";
        if ($this->load($data) && $this->validate()) {
            //做点有意义的事
            $time = time();
            $token = $this->createToken($data['Admin']['adminuser'], $time);
            $mailer = Yii::$app->mailer->compose('seekpass', ['adminuser' => $data['Admin']['adminuser'], 'time' => $time, 'token' => $token]);
            $mailer->setFrom("itarvin@163.com");
            $mailer->setTo($data['Admin']['adminemail']);
            $mailer->setSubject("商城演示-找回密码");
            if ($mailer->send()) {
                return true;
            }
        }
        return false;
    }
    public function createToken($adminuser, $time)
    {
        return md5(md5($adminuser).base64_encode(Yii::$app->request->userIP).md5($time));
    }
    public function reg($data)
    {
        $this->scenario = 'adminadd';
        if ($this->load($data) && $this->validate()) {
            $this->adminpass = md5(md5($this->adminpass));
            $this->createtime = time();
            if ($this->save(false)) {
                return true;
            }
            return false;
        }
        return false;
    }
    public function changeEmail($data)
    {
        $this->scenario = "changeemail";
        if ($this ->load($data) && $this->validate())
        {
            return (bool)$this->updateAll(['adminemail' => $this ->adminemail],'adminuser =:user',[':user' => $this->adminuser]);
        }
        return false;
    }
    public function changePass($data)
    {
        $this ->scenario = "changepass";
        if ($this->load($data) && $this->validate())
        {
            return (bool)$this->updateAll(['adminpass'=>md5(md5($this->adminpass))]);
        }
        return false;
    }
}
