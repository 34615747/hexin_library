<?php
namespace Hexin\Library\Model;

use App\Http\Repositories\BaseRepository;
use App\Scopes\MerchantIdScope;
use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LinLancer\Laravel\EloquentModel;


class Model extends EloquentModel
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
