<?php
namespace App\Http\Controllers\Base;

use App\Http\Repositories\Base\ApprovalConfigRepository;
use hg\apidoc\annotation as Apidoc;

use App\Exceptions\ValidatorException;
use App\Http\Controllers\Controller;
use App\Http\Repositories\Repository;
use Illuminate\Http\Request;

/**
 * @Apidoc\Title("审批流配置")
 * @Apidoc\Group("Base")
 * @Apidoc\Sort("1")
 */
class ApprovalConfigController extends Controller
{
    protected $repository;

    public function __construct(ApprovalConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    // 获取审批流开启状态
    /**
     * @Apidoc\Title("获取审批流开启状态")
     * @Apidoc\Url("purchase/approval/get_approval_status")
     * @Apidoc\Method("GET,POST,PUT,PATCH,DELETE")
     * @Apidoc\Tag("lang(apidoc.tag.finish)")
     * @Apidoc\Param("type", type="int", require=true, desc="类型：1采购审批流 2质检审批流 3供应商对账审批 4折扣单流水审批流")
     * @Apidoc\Returned("status", type="int", desc="审批流控制状态：1开启 2关闭 0删除")
     * @Apidoc\Returned("use_condition", type="int", desc="否开启条件控制1开启 2关闭")
     * @Apidoc\Returned("type", type="int", desc="类型 1=采购审批流 2=质检审批流 3=供应商对账审批")
     * @Apidoc\Returned("create_uuid", type="varchar", desc="创建人uuid")
     * @Apidoc\Returned("create_name", type="varchar", desc="创建人name")
     * @Apidoc\Returned("create_time", type="datetime", desc="创建时间")
     * @Apidoc\Returned("update_time", type="datetime", desc="更新时间")
     */
    public function getApprovalStatus(Request $request)
    {
        $rules = [
            'type' => 'required'
        ];
        $messages = [
            'type.in' => '1采购审批流2质检审批3供应商对账审批4折扣单流水审批流'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ValidatorException($validator->errors()->all());
        }
        return $this->repository->getApprovalStatus($validator->getData());
    }

    // 设置审批流开启状态
    /**
     * @Apidoc\Title("设置审批流开启状态")
     * @Apidoc\Url("purchase/approval/edit_approval_status")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("lang(apidoc.tag.finish)")
     * @Apidoc\Param("type", type="int", require=true, desc="类型：1采购审批流 2质检审批流 3供应商对账审批 4折扣单流水审批流")
     * @Apidoc\Param("status", type="int", require=true, desc="审批流控制状态：1开启 2关闭")
     */
    public function editApprovalStatus(Request $request)
    {
        $rules = [
            'type' => 'required',
            'status' => 'required|in:1,2' // 1开启 2关闭
        ];
        $messages = [
            'type.in' => '1采购审批流2质检审批3供应商对账审批4折扣单流水审批流'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ValidatorException($validator->errors()->all());
        }
        return $this->repository->editApprovalStatus($validator->getData());
    }

    // 编辑审批设置
    /**
     * @Apidoc\Title("编辑审批设置")
     * @Apidoc\Url("purchase/approval/edit_approval_config")
     * @Apidoc\Method("POST")
     * @Apidoc\Tag("lang(apidoc.tag.finish)")
     * @Apidoc\Param(ref="App\Docs\Api\Base\ApprovalConfigController@editApprovalConfig")
     * @Apidoc\Returned(ref="App\Docs\Api\Base\ApprovalConfigController@editApprovalConfig")
     */
    public function editApprovalConfig(Request $request)
    {
        $rules = [
            'type' => 'required',
            'list' => 'required',
//            'list.*.approval_condition' => 'required|in:1,2,3,4,5,6', // 审批条件 1应付金额 2实付金额 3差价 4运费 5异常件数 6异常金额
//            'list.*.min' => 'required',
//            'list.*.max' => 'required',
            'list.*.formula' => 'nullable',
            'list.*.approval_process' => 'required',
            'list.*.approval_process.*.approval_type' => 'required|in:1,2,3', // 审批类型 1人工审核 2自动通过 3自动拒绝
            'list.*.approval_process.*.approval_title' => 'required',
            'list.*.approval_process.*.approved_by' => 'required_if:list.*.approval_process.*.approval_type,1|in:0,1,2,3|nullable', // 审批人 1指定成员 2直属上级 3多人审核
//            'list.*.approval_process.*.approval_way' => 'nullable|in:1,2', // 审批方式 1或签 2会签
            'list.*.approval_process.*.sort' => 'required',
            'list.*.approval_process.*.approval_process_user' => 'array|nullable',
            'list.*.approval_process.*.approval_process_user.*.approval_uuid' => 'required_if:list.*.approval_process.*.approved_by,1,3|string|nullable',
            'list.*.approval_process.*.approval_process_user.*.approval_name' => 'required_if:list.*.approval_process.*.approved_by,1,3|string|nullable',
        ];
        $messages = [
            'type.in' => '1采购审批流2质检审批流3供应商对账审批4折扣单流水审批流'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ValidatorException($validator->errors()->all());
        }
        return $this->repository->editApprovalConfig($validator->getData());
    }

    // 编辑审批设置

    /**
     * @Apidoc\Title("获取审批设置")
     * @Apidoc\Url("purchase/approval/get_approval_config")
     * @Apidoc\Method("GET")
     * @Apidoc\Tag("lang(apidoc.tag.finish)")
     * @Apidoc\Param("type", type="int", require=true, desc="类型：1采购审批流 2质检审批流 3供应商对账审批 4折扣单流水审批流")
     * @Apidoc\Returned("approval_condition", type="int", desc="审批条件 1应付金额 2实付金额 3差价 4运费 5异常件数 6异常金额 7对账结算金额 8对账计算件数 9不限制")
     * @Apidoc\Returned("formula", type="string", desc="公式")
     * @Apidoc\Returned("min", type="string", desc="最小值")
     * @Apidoc\Returned("max", type="string", desc="最大值")
     * @Apidoc\Returned("approval_process", type="array", desc="审批流程", children={
     *@Apidoc\Returned("approval_type", type="int", desc="审批类型 1人工审核 2自动通过 3自动拒绝"),
     *     @Apidoc\Returned("approval_title", type="string", desc="审批标题"),
     *     @Apidoc\Returned("approved_by", type="int", desc="审批人 1指定成员 2直属上级 3多人审核"),
     *     @Apidoc\Returned("approval_way", type="int", desc="审批方式 1或签 2会签"),
     *     @Apidoc\Returned("sort", type="int", desc="排序"),
     *     @Apidoc\Returned("approval_process_user", type="array", desc="审批人", children={
     *@Apidoc\Returned("approval_uuid", type="string", desc="审批人uuid"),
     *     @Apidoc\Returned("approval_name", type="string", desc="审批人名称"),
     *          *     @Apidoc\Returned("approval_process_id", type="string", desc="审批流程id")
     *     })
     *     })
     */
    public function getApprovalConfig(Request $request)
    {
        $rules = [
            'type' => 'required'
        ];
        $messages = [
            'type.in' => '1采购审批流2质检审批流3供应商对账审批流4折扣单流水审批流'
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ValidatorException($validator->errors()->all());
        }
        return $this->repository->getApprovalConfig($validator->getData());
    }
}