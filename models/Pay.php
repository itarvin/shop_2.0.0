<?php
namespace app\models;

use app\models\Order;
use app\models\OrderDatail;
use app\models\Product;
class Pay 
{
   public static function alipay($orderid)
   {
        $amount = Order::find()->where('orderid = :oid',[':oid' => $orderid])->one()->amount;
        if (!empty($orderid)) {
            $alipay = new \AlipayPay();
            $giftname = "商城test";
            $data = OrderDatail::find()->where('orderid = :oid',[':oid' => $orderid])->all();
            $body = "";
            foreach ($data as $pro) {
                $body .= Product::find()->where('productid = :pid', [':pid' => $pro['productid']])->one()->title . " - ";
            }
            $body .= "等商品";
            $showUrl = "http://itarvin.gotoip2.com";
            $html = $alipay->requestPay($orderid,$giftname,$amount,$body,$showUrl);
            echo $html;
        }
   }
}
