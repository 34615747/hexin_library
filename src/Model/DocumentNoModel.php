<?php
namespace Hexin\Library\Model;

use Illuminate\Support\Facades\Cache;
use App\Exceptions\ApiException;

/**
 * Class DocumentNoModel
 */
abstract class DocumentNoModel extends Model
{
    public $table = 'document_no';

    protected $fillable = [
        'id',
        'merchant_id',
        'no',//单号
        'type',//单号类型
        'model',//模型名
        'create_time',
        'update_time',
    ];

    /**
     * 生成单号所需的参数
     * @var array
     */
    public static $static_params = [];

    /**
     * 单号类型
     */
    const TYPE_PACKAGE_NO = 1;
    public static $typeLabel = [
        self::TYPE_PACKAGE_NO => '示例单号',
    ];


    /**
     * 获得配置集合
     * todo 请重写
     * @return array[]
     */
    public static function getConfigs()
    {
        throw new \Exception([0,'请添加配置']);
        return [
            static::TYPE_EXAMPLE_NO => [//B20231130-000014
                'length'    => 6,//填充的长度
                'time_type'=>'Ymd',//时间类型
                'prefix_fun'=>'getPrefixPackageNo',//获取前缀的方法
                'redis_key_fun'=>'getRedisKeyPackageNo',//获取redis key的方法
            ],
        ];
    }

    /**
     * 获得配置
     * @param $type
     * @return array
     * @throws ApiException
     */
    public static function getConfig($type)
    {
        $conf = static::getConfigs()[$type]??[];
        if(!$type){
            throw new ApiException([0,'该类型'.$type.'未配置']);
        }
        return $conf;
    }

    /**
     * redis缓存key名
     * @return string
     */
    public function getRedisKey($type,$date = '') :string
    {
        $conf = static::getConfig($type);
        $fun = $conf['redis_key_fun'];
        $class = static::class;
        $class = (new \ReflectionClass($class))->newInstanceArgs([]); ;
        return $class->$fun($type,$date);
    }

    /**
     * 单号前缀
     * B20240608-
     * @return string
     * @throws ApiException
     */
    public function getPre($type)
    {
        $conf = static::getConfig($type);
        $fun = $conf['prefix_fun'];
        $class = static::class;
        $class = (new \ReflectionClass($class))->newInstanceArgs([]); ;
        return $class->$fun($type);
    }

    /**
     * 获得序号，填充序号
     * 0000001
     * @param $increment_num //递增数
     * @return string
     */
    public function getSerialNum($type,$increment_num) :string
    {
        return sprintf('%0' . static::getConfig($type)['length'] . 'd', $increment_num);
    }

    /**
     * 获得编号格式
     * @param $format //时间格式 年：Y，月：Ym，日：Ymd
     * @return string
     * @throws ApiException
     */
    public function getNoFormat($type,$model,$static_params=[])
    {
        self::$static_params = $static_params;

        $RedisKey = $this->getRedisKey($type);
        if(!Cache::has($RedisKey)){
            $del_date = $this->getExpiredDate($type);
            if($del_date){
                $del_redis_key = $this->getRedisKey($type,$del_date);
                Cache::forget($del_redis_key);
            }
        }

        $increment_num = Cache::increment($RedisKey);

        $pre = $this->getPre($type);
        $new_serial_num = $this->getSerialNum($type,$increment_num);
        $new_document_no = $pre.$new_serial_num;
        //判断是否存在
        if(static::where([
            'no'   => $new_document_no,
            'type'   => $type,
        ])->exists()) {
            $NewDocumentNoModel = $this->getNewModel($pre,$type);
            $new_serial_num = $NewDocumentNoModel->cutNoSerialNum();
            Cache::forever($RedisKey, intval($new_serial_num));
            return $this->getNoFormat($type,$model,$static_params);
        }
        $documentNoModel = new static();
        $documentNoModel->no          = $new_document_no;
        $documentNoModel->type        = $type;
        $documentNoModel->merchant_id = 2;
        $documentNoModel->model = $model;
        $documentNoModel->saveBefore();
        $documentNoModel->save();
        return $new_document_no;
    }

    /**
     * 获取最新的model
     * @return mixed
     */
    public function getNewModel($pre,$type)
    {
        return static::orderBy('no', 'desc')
            ->where('no','like',$pre.'%')
            ->where('type',$type)
            ->select('no','type')->first();
    }

    /**
     * 截取最新序号
     * @return int
     * @throws ApiException
     */
    public function cutNoSerialNum()
    {
        $conf = static::getConfig($this->type);
        $length = $conf['length'];
        return intval(substr($this->no, -$length));
    }

    /**
     * 保存之前
     */
    public function saveBefore()
    {

    }

    /**
     * 过期的日期
     * 昨天，上月，去年等
     * @return string
     * @throws ApiException
     */
    public function getExpiredDate($type)
    {
        $conf = self::getConfig($type);
        switch ($conf['time_type']) {
            case 'Y':
                //删除去年缓存数据
                $del_day = date($conf['time_type'], strtotime("-1 year"));
                break;
            case 'Ym':
            case 'y-m':
                //删除上月缓存数据
                $del_day = date($conf['time_type'], strtotime("-1 month"));
                break;
            case 'Ymd':
                //删除昨天缓存数据
                $del_day = date($conf['time_type'], strtotime("-1 day"));
                break;
            default:
                break;
        }
        return $del_day??null;
    }
}