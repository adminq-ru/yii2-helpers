<?php

namespace adminq\yii\helpers;

use yii\helpers\BaseArrayHelper;

class ArrayHelper extends BaseArrayHelper
{
    public static function toObject(array $array)
    {
        return json_decode(json_encode($array), false, 512);
    }
}
