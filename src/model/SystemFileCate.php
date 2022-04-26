<?php

namespace Eadmin\model;

use Eadmin\Admin;

class SystemFileCate extends BaseModel
{
    public function __construct(array $data = [])
    {
        $this->table = config(Admin::getAppName(). '.database.file_cate_table');
        parent::__construct($data);
    }
}
