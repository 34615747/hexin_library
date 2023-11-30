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
