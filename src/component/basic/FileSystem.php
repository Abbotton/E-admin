<?php


namespace Eadmin\component\basic;


use Eadmin\component\Component;
use Eadmin\component\form\field\Upload;
use Eadmin\traits\CallProvide;
use think\exception\HttpResponseException;
use think\facade\Request;

/**
 * Class FileSystem
 * @package Eadmin\component\basic
 * @method $this initPath(string $path) 目录路径
 * @method $this data(array $data) 数据
 * @method $this multiple(bool $id = true) 是否多选
 * @method $this uploadFinder(bool $bool = true)
 */
class FileSystem extends Component
{

	protected $name = 'EadminFileSystem';

	public function __construct($data)
	{
        parent::__construct();
        $this->bindAttValue('modelValue', false, true);
        $this->bindAttValue('addParams',[]);
		$this->attr('total', count($data));
		$page = Request::get('page', 1);
		$size = Request::get('size', 100);
		$page = ($page - 1) * $size;
		$data = array_slice($data, $page, $size);
		$this->data($data);
		$this->attr('upload', Upload::create()
			->finder(false)
			->attr('foreverShow', true)
			->disk('local')
			->content(
				Button::create('上传')
                    ->icon('el-icon-upload')
					->sizeMini()
			));
	}

	public function jsonSerialize()
	{
		if (request()->has('ajax_request_data')) {
            throw new HttpResponseException(json(['code' => 200, 'data' => $this->attr('data'), 'total' => $this->attr('total')]));
		}
		return parent::jsonSerialize(); // TODO: Change the autogenerated stub
	}
}
