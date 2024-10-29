<?php

namespace Hexin\Library\Helpers;

class CountryHelper
{
    /**
     * 获取省份信息
     * @param $params
     * @return mixed|null
     */
    public static function getProvince($params)
    {
        $return = [
            "province_code" => '', "province_name" => ''
        ];
        if (empty($params['country_code']) || $params['country_code'] != 'US') {
            return $return;
        }
        if (empty($params['postal_code']) && empty($params['province_code']) && empty($params['province_name'])) {
            return $return;
        }
        $data = [
            ["min_postal_code" => "35004", "max_postal_code" => "36925", "province_code" => "AL", "province_name" => "Alabama"],
            ["min_postal_code" => "99501", "max_postal_code" => "99950", "province_code" => "AK", "province_name" => "Alaska"],
            ["min_postal_code" => "85001", "max_postal_code" => "86556", "province_code" => "AZ", "province_name" => "Arizona"],
            ["min_postal_code" => "71601", "max_postal_code" => "72959", "province_code" => "AR", "province_name" => "Arkansas"],
            ["min_postal_code" => "90001", "max_postal_code" => "96162", "province_code" => "CA", "province_name" => "California"],
            ["min_postal_code" => "80001", "max_postal_code" => "81658", "province_code" => "CO", "province_name" => "Colorado"],
            ["min_postal_code" => "06001", "max_postal_code" => "06928", "province_code" => "CT", "province_name" => "Connecticut"],
            ["min_postal_code" => "19701", "max_postal_code" => "19980", "province_code" => "DE", "province_name" => "Delaware"],
            ["min_postal_code" => "32004", "max_postal_code" => "34997", "province_code" => "FL", "province_name" => "Florida"],//
            ["min_postal_code" => "30002", "max_postal_code" => "39901", "province_code" => "GA", "province_name" => "Georgia"],
            ["min_postal_code" => "96701", "max_postal_code" => "96898", "province_code" => "HI", "province_name" => "Hawaii"],
            ["min_postal_code" => "83201", "max_postal_code" => "83877", "province_code" => "ID", "province_name" => "Idaho"],//
            ["min_postal_code" => "60001", "max_postal_code" => "62999", "province_code" => "IL", "province_name" => "Illinois"],
            ["min_postal_code" => "46001", "max_postal_code" => "47997", "province_code" => "IN", "province_name" => "Indiana"],
            ["min_postal_code" => "50001", "max_postal_code" => "52809", "province_code" => "IA", "province_name" => "Iowa"],//
            ["min_postal_code" => "66002", "max_postal_code" => "67954", "province_code" => "KS", "province_name" => "Kansas"],
            ["min_postal_code" => "40003", "max_postal_code" => "42788", "province_code" => "KY", "province_name" => "Kentucky"],
            ["min_postal_code" => "70001", "max_postal_code" => "71497", "province_code" => "LA", "province_name" => "Louisiana"],//
            ["min_postal_code" => "03901", "max_postal_code" => "04992", "province_code" => "ME", "province_name" => "Maine"],//
            ["min_postal_code" => "20588", "max_postal_code" => "21930", "province_code" => "MD", "province_name" => "Maryland"],//
            ["min_postal_code" => "01001", "max_postal_code" => "05544", "province_code" => "MA", "province_name" => "Massachusetts"],
            ["min_postal_code" => "48001", "max_postal_code" => "49971", "province_code" => "MI", "province_name" => "Michigan"],
            ["min_postal_code" => "55001", "max_postal_code" => "56763", "province_code" => "MN", "province_name" => "Minnesota"],
            ["min_postal_code" => "38601", "max_postal_code" => "39776", "province_code" => "MS", "province_name" => "Mississippi"],
            ["min_postal_code" => "63001", "max_postal_code" => "65899", "province_code" => "MO", "province_name" => "Missouri"],
            ["min_postal_code" => "59001", "max_postal_code" => "59937", "province_code" => "MT", "province_name" => "Montana"],
            ["min_postal_code" => "68001", "max_postal_code" => "69367", "province_code" => "NE", "province_name" => "Nebraska"],
            ["min_postal_code" => "88901", "max_postal_code" => "89883", "province_code" => "NV", "province_name" => "Nevada"],
            ["min_postal_code" => "03031", "max_postal_code" => "03897", "province_code" => "NH", "province_name" => "New Hampshire"],
            ["min_postal_code" => "07001", "max_postal_code" => "08989", "province_code" => "NJ", "province_name" => "New Jersey"],
            ["min_postal_code" => "87001", "max_postal_code" => "88441", "province_code" => "NM", "province_name" => "New Mexico"],
            ["min_postal_code" => "00501", "max_postal_code" => "14925", "province_code" => "NY", "province_name" => "New York"],
            ["min_postal_code" => "27006", "max_postal_code" => "28909", "province_code" => "NC", "province_name" => "North Carolina"],
            ["min_postal_code" => "58001", "max_postal_code" => "58856", "province_code" => "ND", "province_name" => "North Dakota"],
            ["min_postal_code" => "43001", "max_postal_code" => "45999", "province_code" => "OH", "province_name" => "Ohio"],
            ["min_postal_code" => "73001", "max_postal_code" => "74966", "province_code" => "OK", "province_name" => "Oklahoma"],
            ["min_postal_code" => "97001", "max_postal_code" => "97920", "province_code" => "OR", "province_name" => "Oregon"],
            ["min_postal_code" => "15001", "max_postal_code" => "19640", "province_code" => "PA", "province_name" => "Pennsylvania"],
            ["min_postal_code" => "02801", "max_postal_code" => "02940", "province_code" => "RI", "province_name" => "Rhode Island"],
            ["min_postal_code" => "29001", "max_postal_code" => "29948", "province_code" => "SC", "province_name" => "South Carolina"],
            ["min_postal_code" => "57001", "max_postal_code" => "57799", "province_code" => "SD", "province_name" => "South Dakota"],
            ["min_postal_code" => "37010", "max_postal_code" => "38589", "province_code" => "TN", "province_name" => "Tennessee"],
            ["min_postal_code" => "73301", "max_postal_code" => "88595", "province_code" => "TX", "province_name" => "Texas"],
            ["min_postal_code" => "84001", "max_postal_code" => "84791", "province_code" => "UT", "province_name" => "Utah"],
            ["min_postal_code" => "05001", "max_postal_code" => "05907", "province_code" => "VT", "province_name" => "Vermont"],
            ["min_postal_code" => "20101", "max_postal_code" => "24658", "province_code" => "VA", "province_name" => "Virginia"],
            ["min_postal_code" => "98001", "max_postal_code" => "99403", "province_code" => "WA", "province_name" => "Washington"],
            ["min_postal_code" => "24701", "max_postal_code" => "26886", "province_code" => "WV", "province_name" => "West Virginia"],
            ["min_postal_code" => "53001", "max_postal_code" => "54990", "province_code" => "WI", "province_name" => "Wisconsin"],
            ["min_postal_code" => "82001", "max_postal_code" => "83414", "province_code" => "WY", "province_name" => "Wyoming"],
            ["min_postal_code" => "20001", "max_postal_code" => "20599", "province_code" => "DC", "province_name" => "District of Columbia"],
            ["min_postal_code" => "00601", "max_postal_code" => "00988", "province_code" => "PR", "province_name" => "Puerto Rico"],
            ["min_postal_code" => "00801", "max_postal_code" => "00851", "province_code" => "VI", "province_name" => "Virgin Islands"],
            ["min_postal_code" => "96910", "max_postal_code" => "96932", "province_code" => "GU", "province_name" => "Guam"],
            ["min_postal_code" => "96799", "max_postal_code" => "96799", "province_code" => "AS", "province_name" => "American Samoa"],
            ["min_postal_code" => "96950", "max_postal_code" => "96952", "province_code" => "MP", "province_name" => "Northern Mariana Islands"]
        ];
        $data = collect($data);
        if (!empty($params['province_code'])) {
            $data = $data->where('province_code', strtoupper($params['province_code']));
        }
        if (!empty($params['province_name'])) {
            $data = $data->where('province_name', ucwords($params['province_name']));
        }
        if (!empty($params['postal_code'])) {
            $info = $data->where('min_postal_code', $params['postal_code'])
                ->where('max_postal_code', $params['postal_code'])
                ->first();
            if ($info) {
                $data = $data->where('min_postal_code', $params['postal_code'])
                    ->where('max_postal_code', $params['postal_code']);
            } else {
                $data = $data->where('min_postal_code', '<=', $params['postal_code'])
                    ->where('max_postal_code', '>=', $params['postal_code']);
            }
            $data = $data->where('min_postal_code', '<=', $params['postal_code'])
                ->where('max_postal_code', '>=', $params['postal_code']);
        }
        return $data ? $data->first() : $return;
    }
}