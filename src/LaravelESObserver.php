<?php

namespace HNP\LaravelES;

use Illuminate\Database\Eloquent\Model;
use HNP\LaravelES\Jobs\AddES;
use HNP\LaravelES\Jobs\UpdateES;
use HNP\LaravelES\Jobs\RemoveES;
// import Log facade
use Illuminate\Support\Facades\Log;

class LaravelESObserver
{

    public function saved(Model $model)
    {
        Log::info('LaravelESObserver: saved');

        if ($model->allowIndex()) {
            try {

                $model->removeFromIndex();
                Log::info('LaravelESObserver: removeFromIndex', $model->toArray());
            } catch (\Exception $e) {
            }
            if (config('hnp_es.queue', false)) {
                AddES::dispatch($model);
            } else {
                $model->addToIndex();
            }
        }
    }
    // public function updated(Model $model)
    // {
    //     Log::info('LaravelESObserver: updated');
    //     if ($model->allowIndex()) {
    //         if (config('hnp_es.queue', false)) {
    //             UpdateES::dispatch($model);
    //         } else {
    //             $model->addToIndex();
    //         }
    //     }
    // }
    public function deleting(Model $model)
    {
        Log::info('LaravelESObserver: deleting');
        try {
            $model->removeFromIndex();
        } catch (\Exception $e) {
        }

        // RemoveES::dispatch($model);
    }
}
