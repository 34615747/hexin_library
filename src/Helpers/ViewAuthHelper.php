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
    const VIEW_GOODS_INTERCOMPANY_PRICE = 16;//公司间定价
    const VIEW_FINANCE_COST = 17;//财务成本
    const VIEW_CONSIGNEE_INFO = 18;//收件人信息
    const VIEW_CUSTOMS_PRICE = 19;//关务大表价格
    const VIEW_INVENTORY_PRICE = 20;//盘点成本单价
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
        self::VIEW_GOODS_INTERCOMPANY_PRICE => '公司间定价',
        self::VIEW_FINANCE_COST => '财务成本',
        self::VIEW_CONSIGNEE_INFO => '收件人信息',
        self::VIEW_CUSTOMS_PRICE => '关务大表价格',
        self::VIEW_INVENTORY_PRICE => '盘点成本单价',
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
            return array_keys(self::$viewLabel);
        }
        $res = Cache::store('redis_common')->get('hexin_site:auth:view:'.$uuid);
        if(!$res){
            $res = '';
            //没设置，所以是空
//            $res = YarHelper::call(['uuid'=>$uuid],YarHelper::yarGetViewAuthConf());
        }
        $res = explode(',',$res);
        self::$auths = $res;
        return $res;
    }

    /**
     * 是否含有权限
     * User: lir 2020/12/25 11:19
     * @return bool
     */
    public static function isAuth($view_id,$uuid='')
    {
        $auths = self::getAuths($uuid);
        if(in_array($view_id,$auths)){
            return true;
        }
        return false;
    }

    /**
     * 显示供应商名称
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewSupplierName($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_SUPPLIER_NAME,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示旺旺
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewSupplierWangwang($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_SUPPLIER_WANGWANG,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示店铺地址
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewSupplierStoreAddr($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_SUPPLIER_STORE_ADDR,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示开发价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewPurchaseDevelopmentPrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_PURCHASE_DEVELOPMENT_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示价格区间
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewPurchasePriceBetween($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_PURCHASE_PRICE_BETWEEN,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示运费
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewPurchaseFreight($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_PURCHASE_FREIGHT,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示优惠
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewPurchasePreferential($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_PURCHASE_PREFERENTIAL,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示商品链接
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewGoodsUrl($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_GOODS_URL,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示开发价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewGoodsDevelopmentPrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_GOODS_DEVELOPMENT_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示最新采购价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewGoodsPurchasePrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_GOODS_PURCHASE_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示金额相关
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewPurchaseAboutAmount($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_PURCHASE_ABOUT_AMOUNT,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示公司间定价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewGoodsIntercompanyPrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_GOODS_INTERCOMPANY_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示财务成本价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewFinanceCostAmount($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_FINANCE_COST,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * Desc: 收件人信息
     * Author: @zyouan
     * Date: 2024/7/11
     * @param $str
     * @param string $hide
     * @param string $uuid
     * @return mixed|string
     */
    public static function viewConsigneeInfo($str,  $hide='***', $uuid='')
    {
        return self::isViewConsigneeInfoAuth($uuid) ? $str : $hide;
    }

    /**
     * Desc: 是否有查看收件人信息权限
     * Author: @zyouan
     * Date: 2024/7/11
     * @param string $uuid
     * @return bool
     */
    public static function isViewConsigneeInfoAuth($uuid = '')
    {
        return self::isAuth(self::VIEW_CONSIGNEE_INFO, $uuid);
    }


    /**
     * 显示关务大表价格
     * @param $str
     * @param string $hide
     * @param string $uuid
     * @return mixed|string
     */
    public static function viewCustomsPrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_CUSTOMS_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }

    /**
     * 显示盘点成本价
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function viewInventoryPrice($str,$hide='***',$uuid='')
    {
        if(self::isAuth(self::VIEW_INVENTORY_PRICE,$uuid)){
            return $str;
        }
        return $hide;
    }
}