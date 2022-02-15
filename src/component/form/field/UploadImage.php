<?php


namespace Eadmin\component\form\field;


use Eadmin\component\form\FormItem;
use Eadmin\service\FileService;
use think\facade\Request;
use Intervention\Image\ImageManagerStatic as Image;
use think\File;
use think\file\UploadedFile;

/**
 * Class UploadImage
 * @package Eadmin\component\form\field
 * @method $this height($value) 图片框高度
 * @method $this width($value) 图片框宽度
 * @mixin \Intervention\Image\Image
 */
class UploadImage extends Upload
{
    protected $uploadField;
    protected $interventionCalls = [];
    protected $thumbnail = [];

    public function __construct($field = null, string $value = '')
    {
        parent::__construct($field, $value);

        $this->displayType('image')->attr('accept','image/*')->size(80, 80)->isUniqidmd5();
    }

    /**
     * 缩略图
     * @param string|array $name
     * @param int $width 宽度
     * @param int $height 高度
     * @return $this
     */
    public function thumbnail($name = 'thumb', $width = 400, $height = 400)
    {
        if (is_array($name)) {
            foreach ($name as $key => $size) {
                $this->thumbnail[$key] = $size;
            }
        } else {
            $this->thumbnail[$name] = [$width, $height];
        }
        return $this;
    }

    public function setFormItem(FormItem $formItem)
    {
        parent::setFormItem($formItem); // TODO: Change the autogenerated stub
        if ($this->attr('displayType') == 'image') {
            $this->url('/eadmin.rest');
            $this->chunk(false);
            $this->uploadField = $formItem->form()->manyRelation() ? $formItem->form()->manyRelation() . '.' . $formItem->attr('prop') : $formItem->attr('prop');
            $this->attr('params', ['upload_field' => $this->uploadField] + $formItem->form()->getCallMethod());
        }
    }

    public function __call($name, $arguments)
    {
        $this->interventionCalls[] = [
            'method' => $name,
            'arguments' => $arguments,
        ];
        return parent::__call($name, $arguments); // TODO: Change the autogenerated stub
    }

    public function handelUpload()
    {
        if (Request::method() == 'POST' && Request::param('upload_field') == $this->uploadField) {
            $file = Request::file('file');
            $filename = Request::param('filename');
            $saveDir = Request::param('saveDir', '/');
            $isUniqidmd5 = Request::param('isUniqidmd5', false);
            $upType = Request::param('upType', 'local');
            $realPath = $file->getRealPath();
            $ext = $file->extension();

            if (count($this->interventionCalls) > 0) {
                try {
                    $image = Image::make($realPath);
                    foreach ($this->interventionCalls as $call) {
                        call_user_func_array([$image, $call['method']], $call['arguments']);
                    }
                    $file = $image->encode(null, null)->getEncoded();
                } catch (\Exception $exception) {

                }
            }
            $url = FileService::instance()->upload($file, $filename, $saveDir, $upType, $isUniqidmd5);
            $this->uploadThumbnail($url, $realPath, $ext, $saveDir, $upType);
            if (!$url) {
                return json(['code' => 999, 'message' => admin_trans('admin.upload_fail')], 404);
            } else {
                return json(['code' => 200, 'data' => $url], 200);
            }
        } else {
            return false;
        }
    }

    /**
     * 显示尺寸
     * @param int $width 宽度
     * @param int $height 高度
     * @return $this
     */
    public function size($width, $height)
    {
        $this->attr('width', $width);
        $this->attr('height', $height);
        return $this;
    }

    /**
     * 图片建议提示
     * @param int $width 宽度
     * @param int $height 高度
     */
    public function helpSize($width, $height)
    {
        $this->help(admin_trans('admin.upload_image_size') . " $width * $height");
        return $this;
    }

    /**
     * 生成缩略图
     * @param $url
     * @param string $realPath
     * @param string $ext
     * @param $saveDir
     * @param $upType
     */
    protected function uploadThumbnail($url, string $realPath, string $ext, $saveDir, $upType): void
    {
        if (count($this->thumbnail) > 0 && $url) {
            foreach ($this->thumbnail as $name => $size) {
                $image = Image::make($realPath);
                list($width, $height) = $size;
                $content = $image->resize($width, $height)->encode();
                $filename = basename($url);
                $search = '.' . $ext;
                $position = strrpos($filename, $search);
                $filename = substr_replace($filename, '', $position, strlen($search));
                $filename = $filename . '-' . $name . '.' . $ext;
                FileService::instance()->upload($content, $filename, $saveDir, $upType);
            }
        }
    }
}
