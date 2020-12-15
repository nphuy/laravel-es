<?php
function hnp_config($key)
{
    return config('hnp_es.' . $key);
}