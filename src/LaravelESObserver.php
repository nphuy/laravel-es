<?php

namespace HNP\LaravelES;
use Illuminate\Database\Eloquent\Model;
use HNP\LaravelES\Jobs\AddES;
use HNP\LaravelES\Jobs\UpdateES;
use HNP\LaravelES\Jobs\RemoveES;

class LaravelESObserver{

    public function created(Model $model)
    {
        AddES::dispatch($model);
    }
    public function updated(Model $model)
    {
        UpdateES::dispatch($model);
    }
    public function deleting(Model $model)
    {
        $model->removeFromIndex();
        // RemoveES::dispatch($model);
    }
}