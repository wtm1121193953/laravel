<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/10
 * Time: 14:36
 */

namespace App\Support;

use Intervention\Image\Gd\Font;
use \Intervention\Image\Image;
use SplFileInfo;


/**
 * 图片处理
 * Class Image
 * @package App\Support
 */
class ImageTool
{

    /**
     * 创建一个画布
     * @param int $width
     * @param int $height
     * @param string $background
     * @return Image
     */
    public static function canvas($width, $height, $background = '#ffffff')
    {
        return \Intervention\Image\Facades\Image::canvas($width, $height, $background);
    }

    /**
     * 给图片添加水印
     * @param Image $image 底图
     * @param Image|string|resource|SplFileInfo|\Imagick $water 水印图片路径或图片对象或图片内容
     * @param string $position
     * @param int $x 距左边距离
     * @param int $y 距右边距离
     * @return Image
     */
    public static function water(Image $image, $water, string $position = 'top-left', int $x = 0, int $y = 0)
    {
        $image->insert($water, $position, $x, $y);
        return $image;
    }

    /**
     * 给图片添加文字
     * @param Image $image
     * @param string $text
     * @param int $size 字体大小
     * @param int $x 文字中心位置
     * @param int $y 文字中心位置
     * @param string $position
     * @param string $color
     * @return Image
     */
    public static function text(Image $image, string $text, int $size = 10, int $x=0, int $y=0, $position = 'center', $color='#000000')
    {

        $image->text($text, $x, $y, function(Font $font) use ($size, $position, $color) {
            $font->file(public_path('../resources/fonts/MSYH.TTC'));
            $font->size($size);
            $font->align($position);
            $font->color($color);
        });
        return $image;
    }
}