<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2021-01-06
 * Time: 23:30
 */

namespace Eadmin\component\basic;

use Eadmin\component\form\Field;

/**
 * 标签页
 * Class Tabs
 * @package Eadmin\component\basic
 * @method $this closable(bool $bool) 标签是否可关闭
 * @method $this addable(bool $bool) 标签是否可增加
 * @method $this editable(bool $bool) 标签是否同时可增加和关闭
 * @method $this stretch(bool $bool) 标签的宽度是否自撑开
 * @method $this type(string $value) 风格类型 card/border-card
 * @method $this tabPosition(string $value) 选项卡所在位置 top/right/bottom/left
 */
class Tabs extends Field
{
    protected $name = 'ElTabs';
    public function pane(){

    }
}