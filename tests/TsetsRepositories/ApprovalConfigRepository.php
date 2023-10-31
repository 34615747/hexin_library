<?php

namespace TsetsRepositories;

use App\Exceptions\ApiException;
use App\Http\Repositories\BaseRepository;
use App\Http\Repositories\Finance\SupplierReconciliationApprovalRepository;
use App\Http\Repositories\Discount\DiscountAccountStatementApprovalRepository;
use App\Http\Repositories\Purchase\OrderApprovalRepository;
use App\Http\Repositories\Purchase\OrderRepository;
use App\Http\Repositories\QualityInspection\QualityAbnormalRepository;
use App\Http\Repositories\QualityInspection\QualityApprovalRepository;
use App\Models\Approval\ApprovalConfigModel;
use App\Models\Discount\DiscountAccountStatementModel;
use App\Models\Purchase\Order;
use App\Models\Purchase\SupplierReconciliationModel;
use App\Models\QualityInspection\QualityAbnormalModel;
use Illuminate\Support\Facades\Cache;
use function App\Http\Repositories\Base\getMerchantId;

class ApprovalConfigRepository extends BaseRepository
{
    use \Hexin\Library\Traits\ApprovalConfigRepositoryTrait;
    const FORMAT_BETWEEN = 1;
    const FORMAT_GT = 2;
    const FORMAT_GTE = 3;
    const FORMAT_LT = 4;
    const FORMAT_LTE = 5;

    public $formula = [
        self::FORMAT_BETWEEN => 'between',
        self::FORMAT_GT => '>',
        self::FORMAT_GTE => '>=',
        self::FORMAT_LT => '<',
        self::FORMAT_LTE => '<='
    ];

    const TYPE_PURCHASE = 1;
    const TYPE_QC = 2;
    const TYPE_DISCOUNT = 4;
    const TYPE_SUPPLIER_RECONCILIATION = 3;

    public $type = [
        self::TYPE_PURCHASE => '采购审批流',
        self::TYPE_QC => '质检审批流',
        // 供应商对账审批流
        self::TYPE_SUPPLIER_RECONCILIATION => '供应商对账审批流',
        self::TYPE_DISCOUNT => '折扣单流水审批流',
    ];

    public static function getApprovalConfigStatus($type = 1)
    {
        $redis_key = 'approval_config:' . $type . getMerchantId();

        $res = Cache::get($redis_key);

        if (empty($res)) {
            return $res;
        }

        $status = (new \Hexin\Library\Model\ApprovalConfigModel())->where('type', $type)->value('status') ?? 2;

        Cache::forever($redis_key, $status);

        return $status;
    }

    public function applyToPending($data)
    {
        $redis_key = 'apply_to_pending:' . $data['type'];
        if (Cache::has($redis_key)) {
            throw new ApiException([ApiException::DEFAULT_ERROR_CODE, '上一次应用所有审批未完成，请稍后再试']);
        } else {
            Cache::put($redis_key, 'request', 300);
        }

        switch ($data['type']) {
            case self::TYPE_PURCHASE:
                // 应用至所有待审批 todo
                $order_list = (new Order())->where('status', OrderRepository::STATUS_SUBMIT)->get();

                if (!$order_list->isEmpty()) {
                    foreach ($order_list as $item) {
                        $item->orderApproval()->delete();
                        try {
                            OrderApprovalRepository::createOrderApproval($item);
                        } catch (\Exception $e) {
                            throw new ApiException([ApiException::DEFAULT_ERROR_CODE, $e->getMessage()]);
                        }
                    }
                }
                break;
            case self::TYPE_QC:
                $order_list = (new QualityAbnormalModel())
                    ->where('abnormal_status', QualityAbnormalRepository::ABNORMAL_STATUS_PENDING)->get();

                if (!$order_list->isEmpty()) {
                    foreach ($order_list as $item) {
                        $item->abnormalApproval()->delete();
                        try {
                            QualityApprovalRepository::createApproval($item);
                        } catch (\Exception $e) {
                            throw new ApiException([ApiException::DEFAULT_ERROR_CODE, $e->getMessage()]);
                        }
                    }
                }
                break;
            case self::TYPE_SUPPLIER_RECONCILIATION:
                $config = (new ApprovalConfigModel())->where('type', ApprovalConfigRepository::TYPE_SUPPLIER_RECONCILIATION)->first();
                if ($config->status == ApprovalConfigRepository::FALSE) { // 关闭审批就不应用了
                    Cache::pull($redis_key);
                    return;
                }
                $order_list = (new SupplierReconciliationModel())
                    ->where('reconciliation_status', SupplierReconciliationModel::RECONCILIATION_STATUS_APPROVAL)->get();
                if (!$order_list->isEmpty()) {
                    foreach ($order_list as $item) {
                        $item->approval()->update(['status' => 0]);
                        try {
                            SupplierReconciliationApprovalRepository::createApproval($item);
                        } catch (\Exception $e) {
                            throw new ApiException([ApiException::DEFAULT_ERROR_CODE, $e->getMessage()]);
                        }
                    }
                }
                break;
            case self::TYPE_DISCOUNT:
                //获取需要审批的折扣单流水
                $statement_list = (new DiscountAccountStatementModel())->newQuery()
                    ->where('need_approval', self::TRUE)
                    ->where('status', DiscountAccountStatementModel::STATUS_PENDING)
                    ->get();
                if (!$statement_list->isEmpty()) {
                    foreach ($statement_list as $item) {
                        $item->statementApproval()->delete();
                        $item->statementApprovalUser()->delete();
                        try {
                            DiscountAccountStatementApprovalRepository::createApproval($item);
                        } catch (\Exception $e) {
                            throw new ApiException([ApiException::DEFAULT_ERROR_CODE, $e->getMessage()]);
                        }
                    }
                }
                break;
        }

        Cache::pull($redis_key);
    }
}
