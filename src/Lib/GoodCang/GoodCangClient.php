<?php
namespace Hexin\Library\Lib\GoodCang;

use Hexin\Library\Helpers\ApiException;

class GoodCangClient
{
    private $appToken;
    private $appKey;

    private $url;

    public function __construct($params)
    {
        //获取token
        $this->appToken = $params['app_token'];
        //获取key
        $this->appKey = $params['app_key'];

        $this->url = $params['url'];
    }

    /**
     * 请求接口
     * @param $url
     * @param string $method
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws ApiException
     */
    public function httpClient($url, $method = 'POST', $data = [], $form_params = 'form_params')
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'app-token' => $this->appToken,
                    'app-key' => $this->appKey,
                ],
                'verify' => true,// todo
                'json' => $data,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }

        return $result;
    }

    /**
     * 获取仓库信息
     **/
    public function getWarehouse()
    {
        $url = $this->url . '/public_open/base_data/get_warehouse';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    /**
     * 获取物流产品
     **/
    public function getShippingMethod()
    {
        $url = $this->url . '/public_open/base_data/get_shipping_method';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    /**
     * 获取公司账户
     **/
    public function getAccountList()
    {
        $url = $this->url . '/public_open/base_data/get_account_list';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    // 获取费用类型
    // public_open/base_data/cost_type_list
    public function getCostTypeList()
    {
        $url = $this->url . '/public_open/base_data/cost_type_list';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    // 获取燃油费率
    ///public_open/base_data/fuel_rate_list
    public function getFuelRateList()
    {
        $url = $this->url . '/public_open/base_data/fuel_rate_list';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    // 获取业务基础数据信息
    // public_open/base_data/sys_base_info
    public function getSysBaseInfo()
    {
        $url = $this->url . '/public_open/base_data/sys_base_info';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }

    // 获取异步任务结果列表
    // public_open/base_data/task_status_list
    public function getTaskStatusList()
    {
        $url = $this->url . '/public_open/base_data/task_status_list';

        $res = $this->httpClient($url, 'POST', []);

        return $res;
    }


    // 新建商品 public_open/product/add_product
    public function addProduct($data)
    {
        $url = $this->url . '/public_open/product/add_product';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取SKU标签文件/public_open/product/print_sku
    public function printSku($data)
    {
        $url = $this->url . '/public_open/product/print_sku';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 修改商品
    public function editProduct($data)
    {
        $url = $this->url . '/public_open/product/edit_product';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取商品列表 public_open/product/get_product_sku_list
    public function getProductSkuList($data)
    {
        $url = $this->url . '/public_open/product/get_product_sku_list';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 修改商品状态 public_open/product/modify_product_status
    public function modifyProductStatus($data)
    {
        $url = $this->url . '/public_open/product/modify_product_status';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 创建入库单 public_open/inbound_order/create_grn
    public function createGrn($data)
    {
        $url = $this->url . '/public_open/inbound_order/create_grn';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 编辑入库单 /public_open/inbound_order/modify_grn
    public function modifyGrn($data)
    {
        $url = $this->url . '/public_open/inbound_order/modify_grn';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 废弃入库单 public_open/inbound_order/del_grn
    public function delGrn($data)
    {
        $url = $this->url . '/public_open/inbound_order/del_grn';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取入库单列表 public_open/inbound_order/get_grn_list
    public function getGrnList($data)
    {
        $url = $this->url . '/public_open/inbound_order/get_grn_list';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取入库明细 /public_open/inbound_order/get_grn_detail
    public function getGrnDetail($data)
    {
        $url = $this->url . '/public_open/inbound_order/get_grn_detail';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 打印箱唛 public_open/inbound_order/print_gc_receiving_box
    public function printGcReceivingBox($data)
    {
        $url = $this->url . '/public_open/inbound_order/print_gc_receiving_box';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 创建出库单 public_open/order/create_order
    public function createOrder($data)
    {
        $url = $this->url . '/public_open/order/create_order';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 修改出库单 public_open/order/modify_order
    public function modifyOrder($data)
    {
        $url = $this->url . '/public_open/order/modify_order';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 异常订单修改物流 public_open/order/modify_ex_fulfilment
    public function modifyExFulfilment($data)
    {
        $url = $this->url . '/public_open/order/modify_ex_fulfilment';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 根据订单号获取单票订单信息  public_open/order/get_order_by_code
    public function getOrderByCode($data)
    {
        $url = $this->url . '/public_open/order/get_order_by_code';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 根据参考号获取单票订单信息 public_open/order/get_order_by_ref_code
    public function getOrderByRefCode($data)
    {
        $url = $this->url . '/public_open/order/get_order_by_ref_code';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取出库单、订单列表 public_open/order/get_order_list
    public function getOrderList($data)
    {
        $url = $this->url . '/public_open/order/get_order_list';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取精简订单列表 public_open/order/get_lite_order_list
    public function getLiteOrderList($data)
    {
        $url = $this->url . '/public_open/order/get_lite_order_list';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 取消出库订单 public_open/order/cancel_order
    public function cancelOrder($data)
    {
        $url = $this->url . '/public_open/order/cancel_order';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 轨迹查询 public_open/order/query_tracking_status
    public function queryTrackingStatus($data)
    {
        $url = $this->url . '/public_open/order/query_tracking_status';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 批量轨迹查询 public_open/order/batch_query_tracking_status
    public function batchQueryTrackingStatus($data)
    {
        $url = $this->url . '/public_open/order/batch_query_tracking_status';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取库存 public_open/inventory/get_product_inventory
    public function getProductInventory($data)
    {
        $url = $this->url . '/public_open/inventory/get_product_inventory';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取库存动态列表 /public_open/inventory/get_inventory_log
    public function getInventoryLog($data)
    {
        $url = $this->url . '/public_open/inventory/get_inventory_log';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }

    // 获取物理仓库存 public_open/inventory/get_warehouse_inventory
    public function getWarehouseInventory($data)
    {
        $url = $this->url . '/public_open/inventory/get_warehouse_inventory';

        $res = $this->httpClient($url, 'POST', $data);

        return $res;
    }
}

