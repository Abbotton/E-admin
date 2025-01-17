<?php


namespace Eadmin\component\form;


use Eadmin\component\basic\Button;
use Eadmin\component\Component;
use Eadmin\form\Form;

/**
 * Class FormAction
 * @package Eadmin\component\form\field
 * @property Form $form
 */
class FormAction extends Component
{
    protected $form;
    //提交按钮
    protected $submitButton;
    //重置按钮
    protected $resetButton;
    //取消按钮
    protected $cancelButton;
    //隐藏重置按钮
    protected $hideResetButton = false;
    //隐藏提交按钮
    protected $hideSubmitButton = false;
    //隐藏取消按钮
    protected $hideCancelButton = false;
    //隐藏操作
    protected $hideAction = false;
    //左边自定义内容
    protected $leftAction = [];
    //右边自定义内容
    protected $rightAction = [];
    public function __construct($form)
    {
        parent::__construct();
        $this->form = $form;
        $submitField = $this->form->bindAttr('submit');
        $this->submitButton = Button::create(admin_trans('admin.save'))->sizeMedium()
            ->type('primary')
            ->event('click', [$submitField => true]);
        $this->resetButton = Button::create(admin_trans('admin.reset'))->sizeMedium();
    }

    /**
     * 提交按钮
     * @return Button
     */
    public function submitButton()
    {
        return $this->submitButton;
    }
    /**
     * 提交确认弹窗
     * @param string $message 确认内容
     * @return \Eadmin\component\basic\Confirm
     */
    public function confirm($message)
    {
        $this->submitButton->event = [];
        $this->submitButton = $this->submitButton->confirm($message)->eventConfirm([$this->form->bindAttr('submit') => true]);
        return $this->submitButton;
    }

    /**
     * 重置按钮
     * @return Button
     */
    public function resetButton()
    {
        return $this->resetButton;
    }

    /**
     * 取消按钮
     * @return Button
     */
    public function cancelButton()
    {
        $this->cancelButton = Button::create(admin_trans('admin.cancel'))->sizeMedium();
        return $this->cancelButton;
    }

    /**
     * 隐藏操作
     * @param bool $bool
     */
    public function hide($bool = true){
        $this->hideAction = $bool;
    }
    /**
     * 隐藏取消按钮
     */
    public function hideCancelButton($bool = true)
    {
        $this->hideCancelButton = $bool;
    }

    /**
     * 隐藏提交按钮
     */
    public function hideSubmitButton($bool = true)
    {
        $this->hideSubmitButton = $bool;
    }

    /**
     * 隐藏重置按钮
     */
    public function hideResetButton($bool = true)
    {
        $this->hideResetButton = $bool;
    }

    /**
     * 添加左边自定义内容
     * @param mixed $content
     */
    public function addLeftAction($content)
    {
        $this->leftAction[] = $content;
    }

    /**
     * 添加右边自定义内容
     * @param mixed $content
     */
    public function addRightAction($content)
    {
        $this->rightAction[] = $content;
    }

    public function render()
    {
        if ($this->hideResetButton) {
            $this->resetButton = null;
        }
        if ($this->hideCancelButton) {
            $this->cancelButton = null;
        }
        if ($this->hideSubmitButton) {
            $this->submitButton = null;
        }
        $this->form->attr('action', [
            'attr'=>$this->attribute,
            'hide' => $this->hideAction,
            'submit' => $this->submitButton,
            'reset' => $this->resetButton,
            'cancel' => $this->cancelButton,
            'leftAction' => $this->leftAction,
            'rightAction' => $this->rightAction,
        ]);
    }
}
