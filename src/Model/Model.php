<?php
namespace Hexin\Library\Model;

use Illuminate\Database\Eloquent\Model as LaravelModel;


class Model extends LaravelModel
{
    const TRUE = 1;
    const FALSE = 2;
    const NEGATIVE = 0;
    const PAGE = 'p';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const MERCHANT_ID = 'merchant_id'; // 商户id

    const DATA_FORMAT = 'Y-m-d H:i:s';

    protected $schema;

    /**
     * 平台
     */
    const PLATFORM_AMAZON = 1;
    const PLATFORM_ALIEXPRESS = 2;
    const PLATFORM_WISH = 3;
    const PLATFORM_EBAY = 4;
    const PLATFORM_JOOM = 6;
    const PLATFORM_WALMART = 8;
    const PLATFORM_SHOPEE = 9;
    const PLATFORM_SHOPIFY = 13;
    const PLATFORM_VOVA = 14;
    const PLATFORM_CDISCOUNT = 15;
    const PLATFORM_LAZADA = 16;
    const PLATFORM_ALIBABA = 17;
    const PLATFORM_DARAZ = 18;
    const PLATFORM_UD = 1000; //手工单暂定1000
    const PLATFORM_HXCART = 22;
    const PLATFORM_TIKTOK = 30;
    const PLATFORM_QVC = 31;
    public static $platformLabel = [
        self::PLATFORM_AMAZON => 'Amazon',
        self::PLATFORM_ALIEXPRESS => 'Aliexpress',
        self::PLATFORM_WISH => 'Wish',
        self::PLATFORM_EBAY => 'Ebay',
        self::PLATFORM_JOOM => 'Joom',
        self::PLATFORM_WALMART => 'Walmart',
        self::PLATFORM_SHOPEE =>'Shopee',
        self::PLATFORM_SHOPIFY =>'Shopify',
        self::PLATFORM_VOVA => 'Vova',
        self::PLATFORM_LAZADA => 'Lazada',
        self::PLATFORM_ALIBABA => 'Alibaba',
        self::PLATFORM_DARAZ => 'Daraz',
        self::PLATFORM_CDISCOUNT => 'Cdiscount',
        self::PLATFORM_HXCART => 'Hxcart',
        self::PLATFORM_UD => 'Ud',
        self::PLATFORM_TIKTOK => 'Tiktok',
        self::PLATFORM_QVC => 'Qvc',
    ];

    /**
     * 增加商户id 默认值
     * @var array
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->{static::MERCHANT_ID} = config('constant.merchant_id');
    }

    /**
     * 获得显示的item_id
     * @param $id
     * @param string $prefix
     * @param int $len
     * @return string
     */
    public static function viewItemId($item_id,$prefix = 'PID',$len = 12)
    {
        return $prefix.str_pad($item_id,$len,'0',STR_PAD_LEFT);
    }




}
