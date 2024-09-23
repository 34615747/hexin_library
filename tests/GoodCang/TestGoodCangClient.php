<?php
namespace Tests\GoodCang;

class TestGoodCangClient extends TestCase
{
    /**
     * @test
     *  获取仓库
     */
    public function testgetWarehouse()
    {
        $GoodCangClient = new \Hexin\Library\Lib\GoodCang\GoodCangClient([
            'app_token' => '',
            'app_key' => '',
            'url' => '']);

        $res = $GoodCangClient->getWarehouse();

        dd($res);
    }
}