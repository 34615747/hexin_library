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
    const PLATFORM_TEMU = 32;
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
        self::PLATFORM_TEMU => 'Temu',
    ];

    /**
     * 平台简码
     * 可添加，别修改，影响单号命名
     */
    const SIMPLE_PLATFORM_AM = 1;
    const SIMPLE_PLATFORM_AE = 2;
    const SIMPLE_PLATFORM_WH = 3;
    const SIMPLE_PLATFORM_EB = 4;
    const SIMPLE_PLATFORM_JM = 6;
    const SIMPLE_PLATFORM_WM = 8;
    const SIMPLE_PLATFORM_SP = 9;
    const SIMPLE_PLATFORM_SY = 13;
    const SIMPLE_PLATFORM_VV = 14;
    const SIMPLE_PLATFORM_CD = 15;
    const SIMPLE_PLATFORM_LZ = 16;
    const SIMPLE_PLATFORM_AI = 17;
    const SIMPLE_PLATFORM_DR = 18;
    const SIMPLE_PLATFORM_UD = 1000;
    const SIMPLE_PLATFORM_HX = 22;
    const SIMPLE_PLATFORM_TT = 30;
    const SIMPLE_PLATFORM_QVC = 31;
    const SIMPLE_PLATFORM_TEMU = 32;
    public static $simplePlatformLabel = [
        self::SIMPLE_PLATFORM_AM => 'AM',
        self::SIMPLE_PLATFORM_AE => 'AE',
        self::SIMPLE_PLATFORM_WH => 'WH',
        self::SIMPLE_PLATFORM_EB => 'EB',
        self::SIMPLE_PLATFORM_JM => 'JM',
        self::SIMPLE_PLATFORM_WM => 'WM',
        self::SIMPLE_PLATFORM_SP => 'SP',
        self::SIMPLE_PLATFORM_SY => 'SY',
        self::SIMPLE_PLATFORM_VV => 'VV',
        self::SIMPLE_PLATFORM_LZ => 'LZ',
        self::SIMPLE_PLATFORM_AI => 'AI',
        self::SIMPLE_PLATFORM_DR => 'DR',
        self::SIMPLE_PLATFORM_CD => 'CD',
        self::SIMPLE_PLATFORM_HX => 'HX',
        self::SIMPLE_PLATFORM_UD => 'UD',
        self::SIMPLE_PLATFORM_TT => 'TT',
        self::SIMPLE_PLATFORM_QVC => 'QV',
        self::SIMPLE_PLATFORM_TEMU => 'TM',
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
