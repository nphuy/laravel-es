<?php
Route::get("hnp/package/es/test", function(){
    dd(HNP\LaravelES\Traits\LaravelES::getClient());
});