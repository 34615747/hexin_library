<?php

namespace Hexin\Library\Jobs;

/**
 * 示例
 * @package App\Jobs
 */
class ExampleJob extends BaseJob
{
    /**
     * 执行内容
     * User: lir 2021/10/25 16:42
     */
    public function runBody()
    {
        $params = $this->JobMessageModel->params;

    }
}
