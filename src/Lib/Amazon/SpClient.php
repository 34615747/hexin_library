<?php

namespace Hexin\Library\Lib\Amazon;


class SpClient
{

    public static $marketplaceIds = [
        // North America
        'CA' => ['NorthAmerica', 'A2EUQ1WTGCTBG2', 'https://www.amazon.ca/', '$', 'CAD'],    //'Canada'
        'US' => ['NorthAmerica', 'ATVPDKIKX0DER', 'https://www.amazon.com/', '$', 'USD'],     //'United States of America'
        'MX' => ['NorthAmerica', 'A1AM78C64UM0Y8', 'https://www.amazon.com.mx/', '$', 'MXN'],    //'Mexico'
        'BR' => ['NorthAmerica', 'A2Q3Y263D00KWC', 'https://www.amazon.com.br/', 'R$', 'BRL'],    //'Brazil'
        //Europe
        'ES' => ['Europe', 'A1RKKUPIHCS9HS', 'https://www.amazon.es/', '€','EUR'],    //'Spain'
        'GB' => ['Europe', 'A1F83G8C2ARO7P', 'https://www.amazon.co.uk/', '￡','GBP'],    //'United Kingdom'
        'IE' => ['Europe', 'A28R8C7NBKEWEA', 'https://www.amazon.co.ie/', '€','EUR'],    //'United Kingdom'
        'FR' => ['Europe', 'A13V1IB3VIYZZH', 'https://www.amazon.fr/', '€','EUR'],    //'France'
        'NL' => ['Europe', 'A1805IZSGTT6HS', 'https://www.amazon.nl/', '€','EUR'],    //'Netherlands'
        'DE' => ['Europe', 'A1PA6795UKMFR9', 'https://www.amazon.de/', '€','EUR'],    //'Germany'
        'IT' => ['Europe', 'APJ6JRA9NG5V4', 'https://www.amazon.it/', '€','EUR'],    //'Italy '
        'SE' => ['Europe', 'A2NODRKZP88ZB9', 'https://www.amazon.se/', 'kr','SEK'],    //'Sweden'
        'TR' => ['Europe', 'A33AVAJ2PDY3EV', 'https://amazon.com.tr/', 'TL','TRY'],    //'Turkey'
        'AE' => ['Europe', 'A2VIGQ35RCS4UG', 'https://amazon.ae/', 'AED','AED'],    //'United Arab Emirates 	'
        'IN' => ['Europe', 'A21TJRUUN4KGV', 'https://www.amazon.in/', '₹','INR'],    //'India'
        'PL' => ['Europe', 'A1C3SOZRARQ6R3', 'https://www.amazon.pl/', 'zł','PLN'],    //'波兰'
        'SA' => ['Europe','A17E79C6D8DWNP', 'https://www.amazon.sa/', 'SAR','SAR'], //'沙特阿拉伯'
        'BE' => ['Europe','AMEN7PMS3EDWL', 'https://www.amazon.com.be/', '€','EUR'], //'比利时'
        //Far East
        'SG' => ['FarEast', 'A19VAU5U5O7RUS', 'https://www.amazon.sg/', 'S$', 'SGD'], //'Singapore'
        'AU' => ['FarEast', 'A39IBJ37TRP1C6', 'https://www.amazon.com.au/', '$', 'AUD'], //'Australia'
        'JP' => ['FarEast', 'A1VC38T7YXB528', 'https://www.amazon.co.jp/', '円', 'JPY'], //'Japan'
    ];

    /**
     * 国家
     * @var array
     */
    public static $country = [
        'US' => '美国',
        'CA' => '加拿大',
        'MX' => '墨西哥',
        'BR' => '巴西',
        'GB' => '英国',
        'FR' => '法国',
        'IT' => '意大利',
        'PL' => '波兰',
        'DE' => '德国',
        'ES' => '西班牙',
        'SE' => '瑞典',
        'NL' => '荷兰',
        'TR' => '土耳其',
        'AE' => '阿联酋',
        'JP' => '日本',
        'IN' => '印度',
        'AU' => '澳大利亚',
        'SG' => '新加坡',
        'SA' => '沙特阿拉伯',
        'BE' => '比利时',
    ];

    /**
     * 区域名称
     * User: lir 2021/11/16 9:19
     * @param $country_code
     * @return string
     */
    public static function areaName($country_code)
    {
        $name = '';
        $area = self::$marketplaceIds[$country_code][0] ?? '';
        switch ($area) {
            case 'NorthAmerica':
                $name = '北美站';
                break;
            case 'Europe':
                $name = '欧洲站';
                break;
            case 'FarEast':
                $name = '远东站';
                break;
        }
        return $name;
    }

    /**
     * 获取销售渠道
     * User: lir 2022/2/9 16:13
     * @param $country_code
     * @return mixed|string
     */
    public static function salesChannel($country_code)
    {
        $salesChannel = [
            'CA' => 'Amazon.ca',
            'US' => 'Amazon.com',
            'MX' => 'Amazon.com.mx',
            'BR' => 'Amazon.com.br',

            'ES' => 'Amazon.es',
            'GB' => 'Amazon.co.uk',
            'FR' => 'Amazon.fr',
            'NL' => 'Amazon.nl',
            'DE' => 'Amazon.de',
            'IT' => 'Amazon.it',
            'SE' => 'Amazon.se',
            'TR' => 'Amazon.tr',
            'AE' => 'Amazon.ae',
            'IN' => 'Amazon.in',
            'PL' => 'Amazon.pl',
            'SA' => 'Amazon.sa',
            'BE' => 'Amazon.com.be',

            'SG' => 'Amazon.sg',
            'AU' => 'Amazon.com.au',
            'JP' => 'Amazon.co.jp',
        ];
        return $salesChannel[$country_code] ?? '';
    }

    /**
     * 获取站点国家
     * @return array
     */
    public static function getAreaCountry()
    {
        $areas = self::$marketplaceIds;
        $data  = [];
        foreach ($areas as $country_code => $area) {
            $data[$area[0]][] = $country_code;
        }
        return $data;
    }

    /**
     * 获取站点国家
     * @return array
     */
    public static function getCountryNameByCode($country_code)
    {
        return self::$country[$country_code] ?? '';
    }

    /**
     * 获取地址
     * User: lir 2021/11/22 11:47
     * @param $country_code
     * @return string
     */
    public static function getUrl($country_code)
    {
        $url = self::$marketplaceIds[$country_code][2] ?? '';
        if (!$url) {
            $url = self::$marketplaceIds['US'][2];
        }
        return $url;
    }

    /**
     * 获取币种符号
     * User: lir 2021/11/22 11:47
     * @param $country_code
     * @return string
     */
    public static function getSymbol($country_code)
    {
        return self::$marketplaceIds[$country_code][3] ?? '';
    }

    /**
     * 获取币种code
     * User: lir 2021/11/22 11:47
     * @param $country_code
     * @return string
     */
    public static function getCurrencyCode($country_code)
    {
        return self::$marketplaceIds[$country_code][4] ?? '';
    }

    /**
     * 获取asin地址
     * User: lir 2022/2/16 17:54
     * @param $country_code
     * @param $asin
     * @return string
     */
    public static function getAsinUrl($country_code, $asin)
    {
        return self::getUrl($country_code) . 'dp/' . $asin;
    }

    /**
     * 获得商品链接地址
     * User: lir 2020/7/17 16:41
     * @return mixed|void
     */
    public static function getGoodsUrl($country_code, $asin)
    {
        $getUrl = SpClient::getUrl($country_code);
        return $getUrl . 'dp/' . $asin;
    }

    /**
     * 获取卖家id 店铺地址
     * User: lir 2022/2/16 17:54
     * @param $country_code
     * @param $asin
     * @return string
     */
    public static function getSellerStoreUrl($country_code, $seller_id)
    {
        return self::getUrl($country_code) . 's?me=' . $seller_id;
    }

    /**
     * 通过站点获取国家
     * User: lir 2022/1/20 15:06
     * @param $marketplaceId
     * @return int|string
     */
    public static function getCountryCodeByMarketplace($marketplaceId)
    {
        foreach (self::$marketplaceIds as $country_code => $marketplace) {
            if ($marketplace[1] == $marketplaceId) {
                return $country_code;
            }
        }
        return '';
    }
}

