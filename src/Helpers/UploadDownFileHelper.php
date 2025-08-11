<?php

namespace Hexin\Library\Helpers;

class UploadDownFileHelper
{
    /**
     * 下载文件by url
     * @param $file_url
     * @param string $path
     * @return string
     */
    public static function downFileByUrl($file_url,$file_name = '',$save_dir = 'download_temp')
    {
        $fileInfo = file_get_contents($file_url);
        $sub_dir =  storage_path('app') . DIRECTORY_SEPARATOR .$save_dir. DIRECTORY_SEPARATOR.date('Y-m-d');

        if (!(is_dir($sub_dir))) {
            mkdir($sub_dir, 0777, 1);
        }

        $pathInfo = pathinfo($file_url);

        $file_name = $file_name ? $file_name : $pathInfo['filename'];
        $ext       = current(explode('?', $pathInfo['extension']));
        $file_path = $sub_dir . DIRECTORY_SEPARATOR . $file_name . '.' . $ext;

        file_put_contents($file_path, $fileInfo);
        $local_path = $save_dir . DIRECTORY_SEPARATOR . $file_name . '.' . $ext;
        return $local_path;
    }

    /**
     * 上传文件by url
     * @param $file_url
     * @param $ext
     * @param string $timestamp
     * @param string $directory
     * @return mixed
     * @throws \Exception
     */
    public static function uploadFileByUrl($file_url ,$ext ='',$directory='Other',$timestamp = '')
    {
        if($ext == ''){
            $pathInfo = pathinfo($file_url);
            $ext       = current(explode('?', $pathInfo['extension']));
        }
        //base_64位加密
        if($file_url != base64_encode(base64_decode($file_url))){
            $file_url = base64_encode(file_get_contents($file_url));
        }
        $params = [
            'source'    => $file_url,
            'ext'       => $ext ,
            'directory' => $directory,
            'timestamp' => $timestamp == '' ? time() : $timestamp,
        ];
        $remote_path = YarHelper::call($params, YarHelper::yarUploadFile());
        return $remote_path;
    }
}
