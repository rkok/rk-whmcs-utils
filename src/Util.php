<?php

namespace RKWhmcsUtils;

class Util
{
    public static function arrayEvery(array $arr, callable $predicate)
    {
        foreach ($arr as $e) {
            if (!call_user_func($predicate, $e)) {
                return false;
            }
        }
        return true;
    }
}
