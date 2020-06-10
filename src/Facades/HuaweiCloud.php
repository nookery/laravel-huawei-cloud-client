<?php

namespace HuaweiCloud\Facades;

use Illuminate\Support\Facades\Facade;

class HuaweiCloud extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'huawei-cloud';
    }
}
