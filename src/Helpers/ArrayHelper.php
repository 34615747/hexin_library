<?php
namespace Hexin\Library\Helpers;

class ArrayHelper
{
    /**
     * 对象转数组
     * @param $object
     * @return mixed
     */
    public static function objectToArray($object) {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }

    /**
     * 一维数组转key-value格式
     * @param $arr
     */
    public static function arrayToKeyValue($arr,$is_show_all=false,$id='id',$name='name'){
        $data = [];
        if($is_show_all){
            $data = [
                [$id=>'',$name=>'全部']
            ];
        }
        foreach ($arr as $key=>$value){
            $data[] = [
                $id=>$key,
                $name=>$value
            ];
        }
        return $data;
    }

    /**
     * 一维数组转key-value格式增强版
     * @param array $arr 基础数据
     * @param bool $is_show_all 是否增加"全部"到数组第一行
     * @param bool $is_others 是否还有其他参数
     * @param string $id 键名
     * @param string $name 键值
     * @param string $other_delimiter 参数分隔符
     * @param string $k_v_delimiter 参数键名键值分隔符
     * @return array
     */
    public static function arrayToKeyValueEnhanced($arr, $is_show_all = false, $is_others = false, $id = 'id', $name = 'name', $other_delimiter = ',', $k_v_delimiter = '|'){
        $data = [];
        if($is_show_all){
            $data = [
                [$id=>'',$name=>'全部']
            ];
        }
        foreach ($arr as $key => $value){
            $data[$key] = [
                $id=> $key,
                $name => $value
            ];
            if($is_others){
                $others = explode($other_delimiter, $value);
                $value = $others[0];
                unset($others[0]);
                $data[$key][$name] = $value;
                foreach($others as $other){
                    list($other_key, $other_value) = explode($k_v_delimiter, $other);
                    if(!empty($other_key)){
                        $data[$key][$other_key] = $other_value;
                    }
                }
            }
        }
        return array_values($data);
    }

    /**
     * 空数组转字符串
     * @param $arr
     * @return string
     */
    public static function emptyArrayToStr($arr)
    {
        if(!$arr){
            return '';
        }
        if(is_array($arr)){
            return json_encode($arr);
        }
        return (string)$arr;
    }

    /**
     * 最简单的XML转数组
     * @param string $xmlstring XML字符串
     * @return array XML数组
     */
    public static function xmlToArray($xmlstring)
    {
        return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
    }

    /**
     * 获得数组列表
     * @param $arr
     * @return array
     */
    public static function getArrList($arr)
    {
        if(!$arr){
            return [];
        }
        if(isset($arr[0])){
            return $arr;
        }
        return [$arr];
    }

    /**
     * 数组转key value格式的
     * User: lir 2020/3/5 11:34
     * @param $arr
     * @param $key 下标的字段
     * @param $val 值的字段
     * @return array
     */
    public static function keyValue($arr,$key='id',$val='')
    {
        $data = [];
        foreach ($arr as $v){
            if($val){
                $data[$v[$key]] = $v[$val];
            }else{
                $data[$v[$key]] = $v;
            }
        }
        return $data;
    }

    /**
     * 按数组某个字段排序
     * User: lir 2020/4/22 15:34
     * @param $arr
     * @param $field
     * @return bool
     */
    public static function sortArrayByField($arr,$field,$sort = SORT_DESC)
    {
        if(!$arr || (count($arr) == 1)){
            return $arr;
        }
        $score = [];
        foreach ($arr as $key => $value) {
            $score[$key] = $value[$field];
        }
        array_multisort($score, $sort, $arr);
        return $arr;
    }

    /**
     * 数组以两个字段排序
     * @param $arr
     * @param $field
     * @return bool
     */
    public static function sortArrayByTwoField($arr,$field1,$feild2,$sort1 = SORT_DESC,$sort2 = SORT_DESC)
    {
        if(!$arr || (count($arr) == 1)){
            return $arr;
        }
        $score1 = [];
        $score2 = [];
        foreach ($arr as $key => $value) {
            $score1[$key] = $value[$field1];
            $score2[$key] = $value[$feild2];
        }
        array_multisort($score1, $sort1, $score2,$sort2, $arr);
        return $arr;
    }

    /**
     * 取出指定的数组的字段的集合
     * User: lir 2020/5/14 20:37
     * @param $data
     * @param string $field
     * @return array
     */
    public static function pluck($data,$field='id')
    {
        $res = [];
        foreach ($data as $item){
            $res[] = $item[$field];
        }
        return $res;
    }

    /**
     * 数组转xml
     * User: lir 2020/7/27 18:53
     * @param $array
     * @param bool $is_add_child_item_tags 是否增加子级元素标签
     * @return string
     */
    public static function arrayToXml($array, $is_add_child_item_tags = true)
    {
        if (is_object($array)) {
            $array = get_object_vars($array);
        }
        $xml = '';
        foreach ($array as $key => $value) {
            $_tag = $key;
            $_id  = null;
            if (is_numeric($key)) {
                $_tag = 'item';
                $_id  = ' id="' . $key . '"';
            }

            //多个xml字段的处理
            $pos = strpos($_tag, '_MULTIFIELD_');
            if (!empty($pos)) {
                $_tag = substr($_tag, 0, $pos);
            }

            //属性的处理
            $pos = strpos($_tag, '_#_');
            if (!empty($pos)) {
                $arr     = explode('_#_', $_tag);
                $val_arr = explode('_#_', $value);
                foreach ($arr as $k => $v) {
                    if ($k == 0) {
                        $_tag = $v;
                        $xml  .= "<{$_tag} ";
                    } else {
                        $xml .= " {$v}=\"{$val_arr[$k]}\" ";
                    }
                }
                $xml .= '>';
                $xml .= $val_arr[0];
                $xml .= "</{$_tag}>";
            } else {
                if(!$is_add_child_item_tags && (is_array($value) && isset($value[0]))){
                    foreach($value as $item){
                        $xml .= "<{$key}>";
                        $xml .= (is_array($item) || is_object($item)) ? self::arrayToXml($item, $is_add_child_item_tags) : ($item);
                        $xml .= "</{$key}>";
                    }
                }else{
                    $xml .= "<{$_tag}{$_id}>";
                    $xml .= (is_array($value) || is_object($value)) ? self::arrayToXml($value, $is_add_child_item_tags) : ($value);
                    $xml .= "</{$_tag}>";
                }
            }
        }

        return $xml;
    }

    /**
     * 多字段排序
     * $arr = sortArrByManyField($array1,'id',SORT_ASC,'name',SORT_ASC,'age',SORT_DESC);
     * User: lir 2020/12/14 17:01
     * @return mixed|null
     * @throws \Exception
     */
    public static function sortArrayByManyField()
    {
        $args = func_get_args(); // 获取函数的参数的数组
        if(empty($args)){
            return null;
        }
        $arr = array_shift($args);
        if(!is_array($arr)){
            throw new \Exception("第一个参数不为数组");
        }
        foreach($args as $key => $field){
            if(is_string($field)){
                $temp = array();
                foreach($arr as $index=> $val){
                    $temp[$index] = $val[$field];
                }
                $args[$key] = $temp;
            }
        }
        $args[] = &$arr;//引用值
        call_user_func_array('array_multisort',$args);
        return array_pop($args);
    }

    /**
     * 数组单行转为多行(适用于导出数据)
     * @param array $arr
     * @param bool $is_reserved_value
     * @return array
     */
    public static function singleLineToMultiLine(array $arr, bool $is_reserved_value = true){
        $datas = [];
        $others = [];
        foreach($arr as $key => &$item){
            if(is_array($item)){
                $tmp = $item[key($item)];
                unset($item[key($item)]);
                $others[$key] = array_values($item);
                $item = $tmp;
            }
        }
        unset($item);
        $others = array_filter($others);
        foreach($others as $k => $v){
            foreach($v as $kk => $vv){
                if(!isset($datas[$kk])){
                    if(!$is_reserved_value){
                        //不保留原数据,则赋空值
                        $datas[$kk] = array_fill_keys(array_keys($arr), '');
                    }else{
                        $datas[$kk] = $arr;
                    }
                }
                $datas[$kk][$k] = $vv;
            }
        }
        $datas = array_merge([$arr], $datas);

        return $datas;
    }

    /**
     * 获取数组指定元素
     * @param array $keys
     * @param array $keys
     * @return array
     */
    public static function getArrayItems(array $arr, array $keys){
        $data = [];
        foreach($keys as $key){
            if(isset($arr[$key])){
                $data[$key] = $arr[$key];
            }
        }
        return $data;
    }

}