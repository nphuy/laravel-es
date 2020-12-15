<?php
namespace HNP\LaravelES\Collections;
use Elasticquent\ElasticquentCollectionTrait;

class ESCollection extends \Illuminate\Database\Eloquent\Collection
{
    use ElasticquentCollectionTrait;
    public function __construct($items, $meta = null)
    {
        // Detect if arguments are old deprecated version ($results, $instance)
        // dd($items);
        // $this->getItems("dsad");
        if (isset($items['hits']) and $meta instanceof \Illuminate\Database\Eloquent\Model) {
            $instance = $meta;
            $meta = $items;
            
            $items = $instance::hydrateElasticsearchResult($meta);
        }

        parent::__construct($items);

        // Take our result meta and map it
        // to some class properties.
        if (is_array($meta)) {
            // dd($meta['hits']['hits']);
            // $this->setMeta($meta);
        }
    }
    public function getKeys(){
        return $this->pluck('id')->toArray();
    }
    public function getModel(){
        return $this->count() ? get_class($this->first()) : null;
    }
    public function query(){
        $model = $this->getModel();
        $ids = $this->getKeys();
        
        $imploded_strings = implode(',', $ids);
        // dd($imploded_strings);
        return $model ? $model::whereIn('id', $ids)->orderByRaw(\DB::raw("FIELD(id, $imploded_strings)")) : null;
    }
    // public function setMeta(array $meta)
    // {
    //     dd($meta);
    //     $ids = collect($meta['hits']['hits'])->pluck('_id');
    //     return $ids;
    //     dd($ids);
    //     dd(collect($meta['hits']['hits'])->pluck('_id'));
    //     $this->took = isset($meta['took']) ? $meta['took'] : null;
    //     $this->timed_out = isset($meta['timed_out']) ? $meta['timed_out'] : null;
    //     $this->shards = isset($meta['_shards']) ? $meta['_shards'] : null;
    //     $this->hits = isset($meta['hits']) ? $meta['hits'] : null;
    //     $this->aggregations = isset($meta['aggregations']) ? $meta['aggregations'] : [];

    //     return $this;
    // }
}