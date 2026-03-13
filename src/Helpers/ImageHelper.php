<?php

namespace Hexin\Library\Helpers;


use Intervention\Image\Facades\Image;

class ImageHelper
{
    /**
     * 压缩图片并转为base64
     * @param string $remoteImageUrl 远程图片地址
     * @param int $rate 压缩质量
     * @return string
     */
    public static function compressImageToBase64($remoteImageUrl,$rate = 60)
    {
        if(empty($remoteImageUrl)) {
            return '';
        }
        try {
            // 获取远程图片内容
            $imageContent = file_get_contents($remoteImageUrl);
        } catch (\Exception $e) {
            throw new \Exception('获取远程图片内容失败');
        }

        // 使用 Intervention 处理图片
        $img = Image::make($imageContent);

        $ext = pathinfo($remoteImageUrl, PATHINFO_EXTENSION);

        // 方法1：调整图片质量（压缩 JPG 质量到 60%）
        $img->encode($ext, $rate); // 第二个参数是质量 0-100

        $base64 = 'data:image/jpeg;base64,'.base64_encode($img);
        return $base64;
    }

}
