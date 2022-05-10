# Laravel mysql index hints scope

### Simple library for mysql index hints and optimisations

## How it's use
#### Extended class Blueprint

`The following methods have been added to the Blueprint class: dropIndexIfExists,hasIndex`

```php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('test_table', function (Blueprint $table) {
    $table->dropIndexIfExists('test_index'); // index will be delete when index exists
    $table->hasIndex('test_index'); // index existence check  
});
```

#### Use trait

```php

use IndexHints\Hintable;

class ExampleModel extends Model
{
    use Hintable;
}

```
#### Functions

If there is no index, then no error will occur.

```php

useIndex(INDEX_NAME, (JOIN|GROUP_BY|ORDER_BY), TABLE_ALIAS);
forceIndex(INDEX_NAME, (JOIN|GROUP_BY|ORDER_BY), TABLE_ALIAS);
ignoreIndex((INDEX_NAME | [INDEX_NAME, INDEX_NAME]), (JOIN|GROUP_BY|ORDER_BY), TABLE_ALIAS);

consts: 
IndexHintsConstants:JOIN;
IndexHintsConstants:GROUP_BY;
IndexHintsConstants:ORDER_BY;

```


#### Examples
```php

/**
* select * from example_models 
* FORCE INDEX (test_index)
*/
ExampleModel::forceIndex('test_index');

/**
* select * from example_models 
* IGNORE INDEX (test_index)
*/

ExampleModel::ignoreIndex('test_index');

/**
 * select * from example_models 
 * USE INDEX (test_index) 
 * IGNORE INDEX (test_index) 
 * USE INDEX (test_index,example_index)
 */
ExampleModel::select('*')
            ->useIndex('test_index')
            ->ignoreIndex('test_index')
            ->useIndex(['test_index', 'example_index']); 

/**
* select * from example_models 
* USE INDEX (example_index)
* IGNORE INDEX FOR ORDER BY (test_index) 
* IGNORE INDEX FOR GROUP BY (test_index)
*/
ExampleModel::select('*')
            ->useIndex(['example_index'])
            ->ignoreIndex('test_index', 'ORDER_BY')
            ->ignoreIndex('test_index', 'GROUP_BY');

/**
*select * from example_models 
*IGNORE INDEX FOR JOIN (example_index)
*IGNORE INDEX FOR ORDER BY (example_index) 
*IGNORE INDEX FOR GROUP BY (example_index)
*/
ExampleModel::select('*')
            ->ignoreIndex('example_index', IndexHintsConstants::JOIN)
            ->ignoreIndex('example_index', IndexHintsConstants::ORDER_BY)
            ->ignoreIndex('example_index', IndexHintsConstants::GROUP_BY);


/**
* Will be exception (However, it is an error to mix USE INDEX and FORCE INDEX for the same table) 
*/
 ExampleModel::select('*')
            ->useIndex('example_index', IndexHintsConstants::JOIN)
            ->forceIndex('example_index', IndexHintsConstants::ORDER_BY)
```


#### Offical MySQL documentation 
Index Hints https://dev.mysql.com/doc/refman/5.7/en/index-hints.html
