<?php

namespace HNP\LaravelES\Traits;

use Elasticquent\ElasticquentTrait;
use Illuminate\Support\Facades\Log;
use HNP\LaravelES\Collections\ESCollection;
use Elasticsearch\ClientBuilder;
use  HNP\LaravelES\LaravelESObserver;

trait LaravelES
{
    public static function bootLaravelES()
    {
        static::observe(app(LaravelESObserver::class));
    }
    private function getIndexName()
    {
        return $this->es_index_name ? $this->es_index_name : $this->getTable();
    }
    private function getSearchField()
    {
        return $this->es_search_fields ? $this->es_search_fields : [];
    }
    public function allowIndex()
    {
        return true;
    }
    private function getClient()
    {
        $hosts =  config('hnp_es.hosts');
        // dd($hosts);
        $client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
        return $client;
    }
    public static function createIndex()
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $settings = $instance->getSettings();
        $mappings = $instance->getMappings();
        // dd($mappings);
        $params = [
            'index' => $index,
            'body'  => [
                'settings' => $settings,
                "mappings" => $mappings
            ]
        ];
        $response = $client->indices()->create($params);
        return $response;
        dd($params);
    }
    public static function deleteIndex()
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $deleteParams = [
            'index' => $index
        ];
        $response = $client->indices()->delete($deleteParams);
        return $response;
    }
    private function getSettings()
    {
        $settings = $this->es_settings;
        if (empty($settings) || !is_array($settings)) {
            return [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                'analysis' => [
                    "analyzer" => [
                        "hnp_analyzer" => [
                            "filter" => ["icu_folding"],
                            "char_filter" => ["html_strip"],
                            "tokenizer" => "icu_tokenizer"
                        ],
                        "default" => [
                            "filter" => [
                                "lowercase",
                                "word_delimiter"
                            ],
                            "char_filter" => [
                                "html_strip",
                                "replace"
                            ],
                            "type" => "custom",
                            "tokenizer" => "whitespace"
                        ]
                    ],
                    "char_filter" => [
                        "replace" => [
                            "type" => "mapping",
                            "mappings" => ["&=> and "]
                        ]
                    ]
                ],

            ];
        }
        return $settings;
    }

    private function getMappings()
    {
        $mappings = $this->es_mappings;
        if (empty($mappings) || !is_array($mappings)) {
            return [];
        }
        return $mappings;
    }
    private function getSize()
    {
        return !empty($this->es_size) ? $this->es_size : 20;
    }
    private function isLimitSearchTime()
    {
        return !empty($this->limit_search_time);
    }
    public function newCollection(array $models = [])
    {
        return new ESCollection($models, self::class);
    }
    public function updateIndex()
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $document_data = $this->getIndexDocumentData();
        $params = [
            'index' => $index,
            'id'    => $this->id,
            'type' => '_doc'
        ];
        $params['body'] = $document_data;

        $response = $client->update($params);
        return $response;
    }
    public function addToIndex()
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $document_data = $this->getIndexDocumentData();
        $params = [
            'index' => $index,
            'id'    => $this->id,
            'type' => '_doc'
        ];
        $params['body'] = $document_data;

        $response = $client->index($params);
        return $response;
    }
    public function removeFromIndex()
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $document_data = $this->getIndexDocumentData();
        $params = [
            'index' => $index,
            'id'    => $this->id,
            'type' => '_doc'
        ];

        $response = $client->delete($params);
        return $response;
    }
    public static function searchWithQuery(array $query)
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $params = [
            'index' => $index,
            'body'  => [
                'size' => $instance->getSize(),
                'query' => $query
            ]
        ];
        $response = $client->search($params);
        $hits = $response['hits']['hits'];
        $resp = collect($response['hits']['hits']);
        $ids = $resp->pluck('_id')->toArray();
        $sources = $resp->pluck('_source')->toArray();
        return new ESCollection($sources, self::class);
    }
    public static function search($key, $size = null)
    {
        $instance = new static;
        $client = $instance->getClient();
        $index = $instance->getIndexName();
        $search_fields = $instance->getSearchField();
        $query = "";
        if ($instance->isLimitSearchTime()) {
            $limit_time = strtotime("-30 days");
            $query = [
                'bool' => [
                    'must' => [
                        'multi_match' => [
                            'query' => $key,
                            'fields' => $search_fields,
                            'type' => 'phrase',
                            'slop' => 150
                        ]
                    ],
                    'filter' => [
                        'range' => [
                            'updated_at' => [
                                'gte' => $limit_time
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $query = [
                'multi_match' => [
                    'query' => $key,
                    'fields' => $search_fields,
                    'type' => 'phrase',
                    'slop' => 150
                ]
            ];
        }
        // dd($query);
        // dd($limit_time);
        $s_size = !empty((int) $size) ? (int) $size : $instance->getSize();
        $params = [
            'index' => $index,
            'body'  => [
                'size' => $s_size,
                'query' => $query
            ]
        ];
        // dd($params);
        $response = $client->search($params);
        $hits = $response['hits']['hits'];
        // dd($hits);
        $resp = collect($response['hits']['hits']);
        $ids = $resp->pluck('_id')->toArray();
        $sources = $resp->pluck('_source')->toArray();
        return new ESCollection($sources, self::class);
        $imploded_strings = implode(',', $ids);
        $model = self::class;
        // dd($model::whereIn('id', $ids)->orderByRaw(\DB::raw("FIELD(id, $imploded_strings)"))->get());
        return $model::whereIn('id', $ids)->orderByRaw(\DB::raw("FIELD(id, $imploded_strings)"))->get();
    }
}
