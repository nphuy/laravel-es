<?php

namespace HNP\LaravelES;

use Illuminate\Database\Eloquent\Model;
use HNP\LaravelES\Jobs\AddES;
use HNP\LaravelES\Jobs\UpdateES;
use HNP\LaravelES\Jobs\RemoveES;

class LaravelESObserver
{

    public function created(Model $model)
    {
        if ($model->allowIndex()) {
            if (config('hnp_es.queue', false)) {
                AddES::dispatch($model);
            } else {
                $model->addToIndex();
            }
        }
    }
    public function updated(Model $model)
    {
        if ($model->allowIndex()) {
            if (config('hnp_es.queue', false)) {
                UpdateES::dispatch($model);
            } else {
                $model->addToIndex();
            }
        }
    }
    public function deleting(Model $model)
    {
        try {
            $model->removeFromIndex();
        } catch (\Exception $e) {
        }

        // RemoveES::dispatch($model);
    }
}
