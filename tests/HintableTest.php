<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use IndexHints\IndexHintsConstants;

class HintableTest extends TestCase
{
    public function testForceIndexes()
    {
        DB::enableQueryLog();

        Schema::create('example_models', function (Blueprint $table) {
            $table->id();
            $table->string('test');
            $table->timestamps();
            $table->index('test');
            $table->index('created_at');
        });

        ExampleModel::create(['test' => 'test']);

        $sql = ExampleModel::select('*')
            ->forceIndex('example_models_test_index')
            ->toSql();


        $this->assertStringContainsString('select * from example_models FORCE INDEX (example_models_test_index)', $sql);

        $sql = ExampleModel::select('*')
            ->ignoreIndex('example_models_created_at_index')
            ->toSql();

        $this->assertStringContainsString('select * from example_models IGNORE INDEX (example_models_created_at_index)', $sql);

        $sql = ExampleModel::select('*')
            ->useIndex(['example_models_test_index', 'example_models_created_at_index'])
            ->ignoreIndex('example_models_created_at_index')
            ->useIndex(['example_models_test_index'])
            ->toSql();

        $this->assertStringContainsString('select * from example_models USE INDEX (example_models_test_index,example_models_created_at_index) IGNORE INDEX (example_models_created_at_index) USE INDEX (example_models_test_index)', $sql);


        $sql = ExampleModel::select('*')
            ->useIndex(['example_models_test_index'])
            ->ignoreIndex('example_models_created_at_index', 'ORDER_BY')
            ->ignoreIndex('example_models_created_at_index', 'GROUP_BY')
            ->toSql();

        $this->assertStringContainsString("select * from example_models USE INDEX (example_models_test_index) IGNORE INDEX FOR ORDER BY (example_models_created_at_index) IGNORE INDEX FOR GROUP BY (example_models_created_at_index)", $sql);


        $sql = ExampleModel::select('*')
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::JOIN)
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::ORDER_BY)
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::GROUP_BY)
            ->toSql();

        $this->assertStringContainsString("select * from example_models IGNORE INDEX FOR JOIN (example_models_created_at_index) IGNORE INDEX FOR ORDER BY (example_models_created_at_index) IGNORE INDEX FOR GROUP BY (example_models_created_at_index)", $sql);


        $this->expectException(\Exception::class);

        ExampleModel::select('*')
            ->useIndex('example_models_created_at_index', IndexHintsConstants::JOIN)
            ->forceIndex('example_models_created_at_index', IndexHintsConstants::ORDER_BY)
            ->toSql();

    }
}