<?php

namespace Tests;

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

        Schema::create('example_model_groups', function (Blueprint $table) {
            $table->id();
            $table->string('test');
            $table->string('example_model_id');
            $table->timestamps();
            $table->index('test');
            $table->index('example_model_id');
        });

        $example = ExampleModel::create(['test' => 'test']);

        ExampleModelGroup::create(['example_model_id' => $example->id, 'test' => 'test']);

        $sql = ExampleModel::select('*')
            ->forceIndex('example_models_test_index')
            ->toSql();


        $this->assertStringContainsString('select * from example_models FORCE INDEX (example_models_test_index)', $sql);

        $sql = ExampleModel::select('*')
            ->ignoreIndex('example_models_created_at_index')
            ->toSql();

        $this->assertStringContainsString(
            'select * from example_models IGNORE INDEX (example_models_created_at_index)',
            $sql
        );

        $sql = ExampleModel::select('*')
            ->useIndex(['example_models_test_index', 'example_models_created_at_index'])
            ->ignoreIndex('example_models_created_at_index')
            ->useIndex(['example_models_test_index'])
            ->toSql();

        $this->assertStringContainsString(
            'select * from example_models USE INDEX (example_models_test_index,example_models_created_at_index) IGNORE INDEX (example_models_created_at_index) USE INDEX (example_models_test_index)',
            $sql
        );


        $sql = ExampleModel::select('*')
            ->useIndex(['example_models_test_index'])
            ->ignoreIndex('example_models_created_at_index', 'ORDER_BY')
            ->ignoreIndex('example_models_created_at_index', 'GROUP_BY')
            ->toSql();

        $this->assertStringContainsString(
            "select * from example_models USE INDEX (example_models_test_index) IGNORE INDEX FOR ORDER BY (example_models_created_at_index) IGNORE INDEX FOR GROUP BY (example_models_created_at_index)",
            $sql
        );


        $sql = ExampleModel::select('*')
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::JOIN)
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::ORDER_BY)
            ->ignoreIndex('example_models_created_at_index', IndexHintsConstants::GROUP_BY)
            ->toSql();

        $this->assertStringContainsString(
            "select * from example_models IGNORE INDEX FOR JOIN (example_models_created_at_index) IGNORE INDEX FOR ORDER BY (example_models_created_at_index) IGNORE INDEX FOR GROUP BY (example_models_created_at_index)",
            $sql
        );

        $sql = ExampleModel::query()
            ->select('*')
            ->join(
                DB::raw(
                    ExampleModel::joinTableIndexHint('example_model_groups', 'example_model_groups_test_index', 'emg')
                ),
                'emg.example_model_id',
                'example_models.id'
            )->toSql();

        $this->assertStringContainsString(
            'select * from "example_models" inner join example_model_groups as emg USE INDEX (example_model_groups_test_index) on "emg"."example_model_id" = "example_models"."id"',
            $sql
        );


        try {
            ExampleModel::query()
                ->select('*')
                ->useIndex('example_models_created_at_index', IndexHintsConstants::JOIN)
                ->forceIndex('example_models_created_at_index', IndexHintsConstants::ORDER_BY)
                ->toSql();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }


        $sql = ExampleModel::query()
            ->select('*')
            ->useIndex('example_models_test_index')
            ->join(
                DB::raw(ExampleModel::joinTableIndexHint('example_model_groups', 'example_model_groups_test', 'emg')),
                'emg.example_model_id',
                'example_models.id'
            )->toSql();

        $this->assertStringContainsString(
            'select * from example_models USE INDEX (example_models_test_index) inner join example_model_groups as emg USE INDEX (example_model_groups_test) on "emg"."example_model_id" = "example_models"."id"',
            $sql
        );
    }
}