# Laravel mysql index hints scope
[![Latest Stable Version](https://poser.pugx.org/shaburov/laravel-mysql-index-hints-scope/v)](//packagist.org/packages/shaburov/laravel-mysql-index-hints-scope)
[![Total Downloads](https://poser.pugx.org/shaburov/laravel-mysql-index-hints-scope/downloads)](//packagist.org/packages/shaburov/laravel-mysql-index-hints-scope)
[![License](https://poser.pugx.org/shaburov/laravel-mysql-index-hints-scope/license)](//packagist.org/packages/shaburov/laravel-mysql-index-hints-scope)
[![CircleCI](https://dl.circleci.com/status-badge/img/circleci/GNf1EusXwDtJS6aiSSH1n1/Cz3Z35ZNHF1j3GqzkQ7w4q/tree/master.svg?style=svg)](https://dl.circleci.com/status-badge/redirect/circleci/GNf1EusXwDtJS6aiSSH1n1/Cz3Z35ZNHF1j3GqzkQ7w4q/tree/master)

### Simple library for mysql index hints and optimisations (USE INDEX, FORCE INDEX, IGNORE INDEX)

### requires
* php: ^7.4|^8.0|^8.1|^8.2|^8.3
* doctrine/dbal: ^3.0
* illuminate/database: ^8.0|^9.0|^10.0|^v11.0
* illuminate/support: ^8.0|^9.0|^10.0|^v11.0

### Installation
    composer require shaburov/laravel-mysql-index-hints-scope
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

Index hints give the optimizer information about how to choose indexes during query processing. Index hints, described here, differ from optimizer hints, described in Section 8.9.3, “Optimizer Hints”. Index and optimizer hints may be used separately or together.

Index hints apply only to SELECT and UPDATE statements.

Index hints are specified following a table name. (For the general syntax for specifying tables in a SELECT statement, see Section 13.2.9.2, “JOIN Clause”.) The syntax for referring to an individual table, including index hints, looks like this:

```
tbl_name [[AS] alias] [index_hint_list]

index_hint_list:
    index_hint [index_hint] ...

index_hint:
    USE {INDEX|KEY}
      [FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
  | {IGNORE|FORCE} {INDEX|KEY}
      [FOR {JOIN|ORDER BY|GROUP BY}] (index_list)

index_list:
    index_name [, index_name] ...

```

#### Offical MySQL documentation 
Index Hints https://dev.mysql.com/doc/refman/5.7/en/index-hints.html
