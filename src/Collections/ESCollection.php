<?php
namespace HNP\LaravelES\Collections;
use Elasticquent\ElasticquentCollectionTrait;

class ESCollection extends \Illuminate\Database\Eloquent\Collection
{
    private $instance;
    public function __construct($items, $instance = null)
    {
        $this->instance = $instance;
        parent::__construct($items);
    }

    public function getKeys(){
        return $this->pluck('id')->toArray();
    }
    public function getModel(){
        return $this->count() ? get_class($this->first()) : null;
    }
    public function query(){
        // dd($this->instance);
        $model = $this->instance;
        $ids = $this->getKeys();
        
        
        // dd($imploded_strings);
        $query = $model::whereIn('id', $ids);
        if(count($ids)){
            $imploded_strings = implode(',', $ids);
            $query = $query->orderByRaw(\DB::raw("FIELD(id, $imploded_strings)"));
        }
        return $query;
    }
}