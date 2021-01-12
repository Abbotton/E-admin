<?php


namespace Eadmin\grid;


use Eadmin\component\Component;
use Eadmin\component\grid\Column;
use Eadmin\component\grid\Pagination;
use Eadmin\component\layout\Content;
use think\facade\Request;
use think\helper\Str;
use think\Model;

/**
 * 表格
 * Class Grid
 * @package Eadmin\grid
 * @method $this size(string $size) Radio的尺寸，仅在border为真时有效 medium / small / mini
 * @method $this height(int $height) 高度
 * @method $this maxHeight(int $height) 最大高度
 * @method $this stripe(bool $bool) 是否为斑马纹
 * @method $this border(bool $bool) 是否带有纵向边框
 * @method $this fit(bool $bool) 列的宽度是否自撑开
 * @method $this defaultExpandAll(bool $bool) 是否默认展开所有行
 * @method $this showHeader(bool $bool) 是否显示表头
 * @method $this highlightCurrentRow(bool $bool) 是否要高亮当前行
 * @method $this headerRowStyle(array $value) 表头行样式
 * @method $this rowStyle(array $value) 行样式
 * @method $this cellStyle(array $value) 单元格样式
 * @method $this headerCellStyle(array $value) 表头单元格的 style样式
 * @method $this loadDataUrl(string $value) 设置加载数据url
 * @property Filter $filter
 */
class Grid extends Component
{
    protected $name = 'EadminGrid';

    protected $column = [];

    protected $pagination;
    //是否隐藏分页
    protected $hidePage = false;
    //操作列
    protected $actionColumn;
    //是否隐藏操作列
    protected $hideAction = false;
    //查询过滤
    protected $filter = null;

    protected $drive;
    public function __construct($data)
    {
        if ($data instanceof Model) {
            $this->drive = new \Eadmin\grid\drive\Model($data);
        } else {
            $this->drive = new \Eadmin\grid\drive\Arrays($data);
        }
        //表格表头颜色
        $this->headerCellStyle([
            'background' => 'linear-gradient(to top,#fafafa,#ffffff)',
            'color' => '#606266',
            'borderTop' => 'solid 1px #ededed'
        ]);
        //分页初始化
        $this->pagination = new Pagination();
        $this->pagination->pageSize(20)->background()->layout('total, sizes, prev, pager, next, jumper');
        //操作列
        $this->actionColumn = new Actions($this);
        $this->bindAttValue('modelValue', false);
        $this->loadDataUrl($this->getRequestUrl());
    }

    /**
     * 查询过滤
     * @param $callback
     */
    public function filter($callback)
    {
        if ($callback instanceof \Closure) {
            call_user_func($callback, $this->getFilter());
        }
    }

    public function getFilter()
    {
        if (is_null($this->filter)) {
            $this->filter = new Filter($this->drive->db());
        }
        return $this->filter;
    }

    /**
     * 设置索引列
     * @param string $type 列类型：selection 多选框 ， index 索引 ， expand 可展开的
     * @return Column
     */
    public function indexColumn($type = 'selection', $label = '')
    {
        $column = $this->column('eadminColumnIndex' . $type, $label);
        $column->attr('type', $type);
        return $column;
    }

    /**
     * 操作列定义
     * @param \Closure $closure
     */
    public function actions(\Closure $closure)
    {
        $this->actionColumn->setClosure($closure);
    }

    /**
     * 隐藏操作列
     * @param bool $bool
     */
    public function hideAction(bool $bool = true)
    {
        $this->hideAction = $bool;
    }

    /**
     * 隐藏工具栏
     * @param bool $bool
     */
    public function hideTools(bool $bool = true)
    {
        $this->attr('hideTools', $bool);
    }

    /**
     * 关闭分页
     */
    public function hidePage(bool $bool = true)
    {
        $this->hidePage = $bool;
    }
    /**
     * 快捷搜索
     */
    public function quickSearch(bool $bool = true)
    {
        $this->attr('quickSearch', $bool);
    }

    /**
     * 分页组件
     * @return Pagination
     */
    public function pagination(){
        return $this->pagination;
    }
    /**
     * 设置分页每页限制
     * @Author: rocky
     * 2019/11/6 14:01
     * @param $limit
     */
    public function setPageLimit($limit)
    {
        $this->pagination->pageSize($limit);
    }

    /**
     * 添加表格列
     * @param string $field 字段
     * @param string $label 显示的标题
     * @return Column
     */
    public function column(string $field = '', string $label = '')
    {
        $column = new Column($field, $label);
        $this->column[] = $column;
        $this->realiton($field);
        return $column;
    }

    /**
     * 解析列返回表格数据
     * @param $datas 数据源
     * @return array
     */
    protected function parseColumn($datas)
    {
        //添加操作列
        if (!$this->hideAction) {
            $this->column[] = $this->actionColumn->column();
        }
        $tableData = [];
        //解析行数据
        foreach ($datas as $data) {
            $row = [];
            foreach ($this->column as $column) {
                $field = $column->attr('prop');
                $row[$field] = $column->row($data);
            }
            if (!$this->hideAction) {
                $actionColumn = clone $this->actionColumn;
                $actionColumn->row($data);
                $row['EadminAction'] = $actionColumn;
            }
            $tableData[] = $row;
        }
        $field = Str::random(15, 3);
        $this->bind($field, $tableData);
        $this->bindAttr('data', $field);
        return $tableData;
    }

    public function getRequestUrl()
    {
        $requestUrl = substr(request()->baseUrl(), 1);
        $requestUrl = preg_replace("/(\/[\d]*\/edit\.rest)$/U", '', $requestUrl);
        $requestUrl = str_replace(['/create.rest', '.rest',], ['', '', ''], $requestUrl);
        if (!empty(request()->action())) {
            $requestUrl = str_replace('/' . request()->action(), '', $requestUrl);
        }
        return $requestUrl;
    }
    public function jsonSerialize()
    {
        //快捷搜索
        $keyword = Request::get('quickSearch', '', ['trim']);
        $this->drive->quickFilter($keyword,$this->column);
        //查询视图
        if (!is_null($this->filter)) {
            $form = $this->filter->render();
            $form->eventSuccess([$this->bindAttr('modelValue') => true]);
            $this->bindAttr('filterForm', $form->bindAttr('model'));
            $this->attr('filter', $form);
            $this->attr('filterField', $form->bindAttr('model'));
        }
        //总条数
        $this->pagination->total($this->drive->getTotal());
        //是否分页
        $page = Request::get('page', 1);
        $size = Request::get('size', $this->pagination->attr('pageSize'));
        if (!$this->hidePage) {
            $this->attr('pagination', $this->pagination->attribute);
        }
        $data = $this->drive->getData($this->hidePage,$page,$size);
        //解析列
        $data = $this->parseColumn($data);
        if (request()->has('build_request_type')) {
            return ['code' => 200, 'data' => $data,'total'=>$this->pagination->attr('total')];
        } else {
            $this->attr('columns', array_column($this->column, 'attribute'));
            return parent::jsonSerialize(); // TODO: Change the autogenerated stub
        }
    }
}
