<?php
namespace Hexin\Library\Helpers;
use Illuminate\Support\Facades\Cache;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class ViewAuthHelper
{

    /**
     * 浏览权限
     */
    const VIEW_SUPPLIER_NAME = 1;//供应商名称
    const VIEW_SUPPLIER_WANGWANG = 2;//旺旺
    const VIEW_SUPPLIER_STORE_ADDR = 3;//店铺地址
//    const VIEW_PURCHASE_ERP_PURCHASE = 4;//ERP采购
    const VIEW_PURCHASE_DEVELOPMENT_PRICE = 5;//开发价
    const VIEW_PURCHASE_PRICE_BETWEEN = 6;//价格区间
    const VIEW_PURCHASE_FREIGHT = 7;//运费
//    const VIEW_PURCHASE_1688_PRICE = 8;//1688单价
    const VIEW_PURCHASE_PREFERENTIAL = 9;//优惠
//    const VIEW_PURCHASE_SUM = 10;//合计
    const VIEW_GOODS_URL = 11;//商品链接
    const VIEW_GOODS_DEVELOPMENT_PRICE = 12;//开发价
    const VIEW_GOODS_PURCHASE_PRICE = 13;//最新采购价
    const VIEW_PURCHASE_ABOUT_AMOUNT = 15;//金额相关
    const VIEW_FINANCE_COST = 17;//财务成本
    public static $viewLabel = [
        self::VIEW_SUPPLIER_NAME => '供应商名称',
        self::VIEW_SUPPLIER_WANGWANG => '旺旺',
        self::VIEW_SUPPLIER_STORE_ADDR => '店铺地址',
//        self::VIEW_PURCHASE_ERP_PURCHASE => 'ERP采购',
        self::VIEW_PURCHASE_DEVELOPMENT_PRICE => '开发价',
        self::VIEW_PURCHASE_PRICE_BETWEEN => '价格区间',
        self::VIEW_PURCHASE_FREIGHT => '运费',
//        self::VIEW_PURCHASE_1688_PRICE => '1688单价',
        self::VIEW_PURCHASE_PREFERENTIAL => '优惠',
//        self::VIEW_PURCHASE_SUM => '合计',
        self::VIEW_GOODS_URL => '商品链接',
        self::VIEW_GOODS_DEVELOPMENT_PRICE => '开发价',
        self::VIEW_GOODS_PURCHASE_PRICE => '最新采购价',
        self::VIEW_PURCHASE_ABOUT_AMOUNT => '金额相关',
        self::VIEW_FINANCE_COST => '财务成本',
    ];

    /**
     * 人员的所有权限
     * @var array
     */
    public static $auths = [];

    /**
     * 获取对应人员的所有权限
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function getAuths($uuid='')
    {
        if(self::$auths){
            return self::$auths;
        }
        if(!$uuid){
            $uuid = CommonHelper::getUUid();
        }
        if(!$uuid){
            throw new \Exception('人员uuid不能为空');
        }
        $res = Cache::store('redis_common')->get('hexin_site:auth:view:'.$uuid);
        if(!$res){
            //todo rpc获取
            $res = '';
        }
        $res = explode(',',$res);
        self::$auths = $res;
        return $res;
    }

    /**
     * 显示财务成本价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewFinanceCostAmount($amount,$hide='***',$uuid='')
    {
        if(self::isFinanceCostAuth($uuid)){
            return $amount;
        }
        return $hide;
    }

    /**
     * 是否含有财务成本权限
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function isFinanceCostAuth($uuid='')
    {
        $auths = self::getAuths($uuid);
        if(in_array(self::VIEW_FINANCE_COST,$auths)){
            return true;
        }
        return false;
    }


}