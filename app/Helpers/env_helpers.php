<?php

if (! function_exists('if_production')) {
    function if_production($realValue, $fakeValue)
    {
        if (app()->environment('production')) {
            return $realValue;
        } else {
            return $fakeValue instanceof Closure ? $fakeValue() : $fakeValue;
        }
    }
}
