<?php
namespace app\models;

use yii\db\ActiveRecord;
use Yii;
use yii\Helpers\ArrayHelper;

class Category extends ActiveRecord
{
    public static function tableName()
    {
        return "{{%category}}";
    }
    public function attributeLabels()
    {
        return [
            'parentid' => '上级分类',
            'title' => '分类名称'
        ];
    }
    //数据验证
    public function rules()
    {
        return [
            ['parentid','required','message' => '上级分类不能为空！'],
            ['title','required','message' => '分类名称不能为空！'],
            ['createtime','safe']
        ];
    }
    public function add($data)
    {
      // 把创建当前时间戳压进数据表
        $data['Category']['createtime'] = time();
        if ($this->load($data) && $this->save()){
            return true;
        }
        return false;
    }
    // 获取所有数据备用
    public function getData()
    {
      //提取当前的模型中的所有数据
        $cates = self::find()->all();
        // 把数据集成数组类型备用
        $cates = ArrayHelper::toArray($cates);
        return $cates;
    }
    // 继承上一方法获取的数据，传递形参
    public function getTree($cates,$pid=0)
    {
      // 定义空数组给树结构
        $tree = [];
        foreach ($cates as $cate) {
          // 每个数据里的父ID若为0，则把该条数据压进tree数组
            if ($cate['parentid'] == $pid) {
                $tree[] = $cate;
                //  array_merge() 将一个或多个数组的单元合并起来，
                // 一个数组中的值附加在前一个数组的后面。返回作为结果的数组。
                // 把拼成的数组给tree数组，循环调getTree方法
                $tree = array_merge($tree, $this->getTree($cates, $cate['cateid']));
            }
        }
        return $tree;
    }
    // 给结构数据赋予前缀标识
    public function setPrefix($data,$p = "|----")
    {
      // 给一个空数组
        $tree = [];
        // 默认前缀个数为1,
        $num = 1;
        $prefix = [0 => 1];
        // current — 返回【data】数组中的当前单元
        // 每个数组中都有一个内部的指针指向它“当前的”单元，初始指向插入到数组中的第一个单元
        while ($val = current($data))
        {
          // 获取data的键值
            $key = key($data);
            // 获取键值大于0
            if ($key > 0) {
              // 若该数据键值-1的父ID ！= 当该数组的第一单元的父id，就给前缀数量自加
                if ($data[$key - 1]['parentid'] != $val['parentid']){
                    $num ++;
                }
            }
            /* 实例
              *$search_array = array('first' => 1, 'second' => 4);
              *if (array_key_exists('first', $search_array)) {
              *   echo "The 'first' element is in the array";
              *}
              */
              // 如果键值在所给的范围里，则前缀数就是所获取的父ID数
            if (array_key_exists($val['parentid'], $prefix)) {
                $num = $prefix[$val['parentid']];
            }
            // 在该数据的标题前连上区分的前缀标识
            /*
            *<?php
            echo str_repeat("-=", 10);
            ?>
            上例将输出：
            -=-=-=-=-=-=-=-=-=-=
            */
            $val['title'] = str_repeat($p, $num).$val['title'];
                                        $prefix[$val['parentid']] = $num;
            $tree[] = $val;
            next($data);
        }
        return $tree;
    }
    // 获取树形列表
    public function getOptions()
    {
        $data = $this ->getData();
        $tree = $this ->getTree($data);
        $tree = $this ->setPrefix($tree);
        $options = ['添加顶级分类'];
        foreach($tree as $cate)
        {
            $options[$cate['cateid']] = $cate['title'];
        }
        return $options;
    }
     public function getTreeList()
    {
        $data = $this ->getData();
        $tree = $this ->getTree($data);
        return $tree = $this->setPrefix($tree);
    }
    public static function getMenu()
    {
        $top = self::find()->where('parentid = :pid', [":pid" => 0])->limit(11)->orderby('createtime asc')->asArray()->all();
        $data = [];
        foreach((array)$top as $k=>$cate) {
            $cate['children'] = self::find()->where("parentid = :pid", [":pid" => $cate['cateid']])->limit(10)->asArray()->all();
            $data[$k] = $cate;
        }
        return $data;
    }
}
