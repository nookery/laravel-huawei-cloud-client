<?php

if (! function_exists('optional')) {
    /**
     * Provide access to optional objects.
     *
     * @param  mixed  $value
     * @param  callable|null  $callback
     * @return mixed
     */
    function optional($value = null, callable $callback = null)
    {
        if (is_null($callback)) {
            if (class_exists('Illuminate\Support\Optional')) {
                return new Illuminate\Support\Optional($value);
            } else {
                return new HuaweiCloud\Support\Optional($value);
            }
            
        } elseif (! is_null($value)) {
            return $callback($value);
        }
    }
}
