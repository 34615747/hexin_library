<?php

namespace Hexin\Library\Traits;

use App\Exceptions\ApiException;
use Hexin\Library\Model\ApprovalConfigModel;
use Hexin\Library\Model\ApprovalLevelModel;
use Hexin\Library\Model\ApprovalProcessModel;
use Hexin\Library\Model\ApprovalProcessUserModel;
use Illuminate\Support\Facades\Cache;

trait ApprovalConfigRepositoryTrait
{
    protected $approvalConfigModel;
    protected $approvalLevelModel;
    protected $approvalProcessModel;
    protected $approvalProcessUserModel;

    public function __construct(ApprovalConfigModel  $approvalConfigModel, ApprovalLevelModel $approvalLevelModel,
                                ApprovalProcessModel $approvalProcessModel, ApprovalProcessUserModel $approvalProcessUserModel)
    {
        $this->approvalConfigModel = $approvalConfigModel;
        $this->approvalLevelModel = $approvalLevelModel;
        $this->approvalProcessModel = $approvalProcessModel;
        $this->approvalProcessUserModel = $approvalProcessUserModel;
    }

    public function getApprovalStatus($data)
    {
        return $this->approvalConfigModel->where('type', $data['type'])->first();
    }

    public function getCurrentyUserUuid()
    {
        return config('userInfo')['uuid'] ?? '';
    }

    public function getCurrentUser()
    {
        return config('userInfo')['member_name'] ?? '';
    }

    public function getReidsKey($type)
    {
        return 'approval_config:' . $type . $this->getMerchantId();
    }

    public function getMerchantId()
    {
        $envMerchantId = env('MERCHANT_ID') ?? '';
        if ($envMerchantId) {
            return $envMerchantId;
        }
        return (int)config('constant.merchant_id');
    }

    public function editApprovalStatus($data)
    {
        $info = $this->approvalConfigModel->where('type', $data['type'])->first();

        if ($info) {
            $info->status = $data['status'];
            $info->use_condition = $data['use_condition'] ?? '';
            $info->update_uuid = $this->getCurrentyUserUuid();
            $info->update_name = $this->getCurrentUser();
            $info->save();
        } else {
            $this->approvalConfigModel->create([
                'status' => $data['status'],
                'use_condition' => $data['use_condition'] ?? '',
                'type' => $data['type'],
                'create_uuid' => $this->getCurrentyUserUuid(),
                'create_name' => $this->getCurrentUser(),
            ]);
        }

        Cache::forever($this->getReidsKey($data['type']), $data['status']);
    }

    public function getApprovalConfigStatus($type = 1)
    {
        $redis_key = $this->getReidsKey($type);

        $res = Cache::get($redis_key);

        if (empty($res)) {
            return $res;
        }

        $status = $this->approvalConfigModel->where('type', $type)->value('status') ?? 2;

        Cache::forever($redis_key, $status);

        return $status;
    }

    public function editApprovalConfig($data)
    {
        // 先删除旧的
        $this->approvalLevelModel->where('type', $data['type'])->update([
            'status' => 0,
            'update_uuid' => $this->getCurrentyUserUuid(),
            'update_name' => $this->getCurrentUser()
        ]);
        foreach ($data['list'] as &$v) {
            $v['type'] = $data['type'];
            $v['create_uuid'] = $this->getCurrentyUserUuid();
            $v['create_name'] = $this->getCurrentUser();
            $info = $this->approvalLevelModel->create($v);
            foreach ($v['approval_process'] as &$vv) {
                $vv['create_uuid'] = $this->getCurrentyUserUuid();
                $vv['create_name'] = $this->getCurrentUser();
                $approval_process = $info->approval_process()->create($vv);

                if (!empty($vv['approval_process_user'])) {
                    foreach ($vv['approval_process_user'] as $cc) {
                        $approval_process->approval_process_user()->create($cc);
                    }
                }
            }
        }

        if (!empty($data['apply_to_pending'])) {
            $this->applyToPending($data);
        }
    }

    // 应用到待审批单据
    public function applyToPending($data)
    {
        throw new ApiException([ApiException::DEFAULT_ERROR_CODE, '请重写applyToPending方法']);
    }

    public function getApprovalConfig($data)
    {
        $list = $this->approvalLevelModel->with([
            'approval_process',
            'approval_process.approval_process_user'
        ])->where('type', $data['type'])->where('status', 1)->get()->toArray();

        if (empty($list)) {
            return $list;
        }

        foreach ($list as &$v) {
            if (in_array($v['formula'], [ApprovalConfigModel::FORMAT_GT, ApprovalConfigModel::FORMAT_GTE]) && $v['max'] == 0) {
                $v['max'] = '*';
            }

            if (in_array($v['formula'], [ApprovalConfigModel::FORMAT_LT, ApprovalConfigModel::FORMAT_LTE]) && $v['min'] == 0) {
                $v['min'] = '*';
            }
        }

        return $list;
    }
}

