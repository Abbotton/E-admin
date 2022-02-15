<?php

namespace Eadmin\service;
use Eadmin\Admin;
use Eadmin\facade\Schedule;
use think\facade\Console;
use think\facade\Db;
use think\facade\Event;

class CrontabService
{
    protected $exec_time = null;
    public function register()
    {
        try {
            $where = ['databackup_on', 'database_number', 'database_day'];
            $config = Db::name('SystemConfig')
                ->whereIn('name', $where)
                ->cache(300)
                ->column('value', 'name');
            Schedule::call('数据库备份和定时清理excel目录', function () use ($config) {
                //数据库备份
                if ($config['databackup_on'] == 1) {
                    BackupData::instance()->backup();
                    $list = BackupData::instance()->getBackUpList();
                    if (count($list) > $config['database_number']) {
                        $backData = array_pop($list);
                        BackupData::instance()->delete($backData['id']);
                    }
                }
                //定时清理excel目录
                $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
                $fileSystem->remove(app()->getRootPath() . 'public/upload/excel');
            })->everyDay($config['database_day']);
            Schedule::call('清理上传已删除文件', function () {
                Admin::file()->clear();
            })->everyMinute();
            //swoole初始化进程执行计划任务
            $this->swooleCrontab();
        } catch (\Exception $exception) {

        }
    }
    public function swooleCrontab()
    {
        if (config('swoole.crontab.enable', false)) {
            Event::listen('swoole.init', function () {
                $process = new \Swoole\Process(function ($pro) {
                    while (true){
                        sleep(1);
                        foreach (\Eadmin\Schedule::$crontab as $crontab){
                            if($crontab['schedule']->isMinuteTask()){
                                if($this->exec_time == date('i')){
                                    continue;
                                }
                                $this->exec_time = date('i');
                            }
                            $process = new \Swoole\Process(function () use($crontab) {
                                Console::call('crontab',['--key='.$crontab['key']]);
                            });
                            $process->start();
                            $process->wait(false);
                        }
                    }
                });
                $process->start();
            });
        }
    }
}
