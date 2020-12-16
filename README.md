# Laravel ES

An easy way to use Elasticsearch in your Laravel applications.

## Installation and Configuration

Install via composer:

```sh
composer require hnp/laravel-es "dev-master"
```

The package's service provider will automatically register its service provider.

Publish the configuration file:

```sh
php artisan vendor:publish --provider="HNP\\LaravelES\LaravelESServiceProvider"
```

After you publish the configuration file as suggested above, you may configure
by adding the following to your application's `.env` file (with appropriate values):

```ini
ELASTIC_HOST=localhost:9200
```

## Preparing your model

The model must implement the following trait:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use HNP\LaravelES\Traits\LaravelES;

class YourModel extends Model
{
    use LaravelES;
}
```

You should add method **getIndexDocumentData** and **es_search_fields** property to your model.

```php
protected $es_search_fields = ["field1", "field2"];
function getIndexDocumentData()
{
    return array(
        'id'      => $this->id,
        'field1'   => $this->field1,
        'field2'   => $this->field2,
        'field3'    =>$this->field3
    );
}
```

## Create an index

Add **es_settings** and **es_mappings** property to your model.

```php
    protected $es_settings = [];
    protected $es_mappings = [];
```

Let's add a new index with some custom settings:

```php
    YourModel::createIndex();
```

## Delete an index

```php
    YourModel::deleteIndex();
```

## Index a document

```php
   $model->addToIndex();
```

## Remove index a document

```php
   $model->removeFromIndex();
```

## Usage

```php
//Simple search
$results = YourModel::search("keywords");

//Search with custom query
$query = [
            'multi_match'=>[
                'query'=>"search keyword",
                'fields'=>["field1", "field2"],
                'type'=>'phrase',
                'slop'=>150
        ]
    ];
$results = YourModel::searchWithQuery($query);

//Get query builder from result
$query = $results->query();
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
