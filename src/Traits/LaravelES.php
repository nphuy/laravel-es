<?php 
namespace HNP\LaravelES\Traits;
use Elasticquent\ElasticquentTrait;
use Illuminate\Support\Facades\Log;
use HNP\LaravelES\Collections\ESCollection;

trait LaravelES
{
    use ElasticquentTrait;

    public static function bootLaravelES()
	{
        static::observe(app(\HNP\LaravelES\LaravelESObserver::class));
    }
    
    function getIndexName()
    {
        return 'hnp';
    }
    // protected $indexSettings = [
    //     'analysis' => [
    //         'char_filter' => [
    //             'replace' => [
    //                 'type' => 'mapping',
    //                 'mappings' => [
    //                     '&=> and '
    //                 ],
    //             ],
    //         ],
    //         'filter' => [
    //             'word_delimiter' => [
    //                 'type' => 'word_delimiter',
    //                 'split_on_numerics' => false,
    //                 'split_on_case_change' => true,
    //                 'generate_word_parts' => true,
    //                 'generate_number_parts' => true,
    //                 'catenate_all' => true,
    //                 'preserve_original' => true,
    //                 'catenate_numbers' => true,
    //             ]
    //         ],
    //         'analyzer' => [
    //             'default' => [
    //                 'type' => 'custom',
    //                 'char_filter' => [
    //                     'html_strip',
    //                     'replace',
    //                 ],
    //                 'tokenizer' => 'whitespace',
    //                 'filter' => [
    //                     'lowercase',
    //                     'word_delimiter',
    //                 ],
    //             ],
    //         ],
    //     ],
    // ];
    public static function searchByQuery($query = null, $aggregations = null, $sourceFields = null, $limit = null, $offset = null, $sort = null)
    {
        $instance = new static;

        $params = $instance->getBasicEsParams(true, $limit, $offset);
        // dd($params);
        if (!empty($sourceFields)) {
            $params['body']['_source']['include'] = $sourceFields;
        }

        if (!empty($query)) {
            $params['body']['query'] = $query;
        }

        if (!empty($aggregations)) {
            $params['body']['aggs'] = $aggregations;
        }

        if (!empty($sort)) {
            $params['body']['sort'] = $sort;
        }
        
        $result = $instance->getElasticSearchClient()->search($params);
        // dd($instance->getElasticSearchClient());
        return static::hydrateElasticsearchResult($result);
    }

    public function getSearchField(){
        return ['content', 'title'];
    }
    public function getIndexDocumentData(){
        return $this->toArray();
    }
    public function newElasticquentResultCollection(array $models = [], $meta = null)
    {
         return new ESCollection($models, $meta);
    }
    public static function quick_search($key){
        
        $instance = new static;
        
        $fields = $instance->getSearchField();
        // dd($fields, ['title','content']);
        // unset($fields['id']);
        $match = array('multi_match' => array('query' => $key, "type"=> "phrase",
        "slop"=> 150));
        $match['multi_match']['fields'] = array_values($fields);

        $filter = ['range' => ['created_at' => ['gt' => strtotime("-30 days")]]];
        $args = ['bool'=>['should'=>$match, 'filter'=>$filter]];
        // dd($args);
        // dd([$fields]);
        return $instance->searchByQuery($args, null, null, 1440);
    }
}