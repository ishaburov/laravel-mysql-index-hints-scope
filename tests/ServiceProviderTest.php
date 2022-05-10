<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServiceProviderTest extends TestCase
{
    public function testDropIndexIfExists()
    {
        DB::enableQueryLog();

        Schema::create('test_table', function (Blueprint $table) {
            $table->id();
            $table->string('test');
            $table->index('test');
        });

        $this->assertEquals("create index \"test_table_test_index\" on \"test_table\" (\"test\")", DB::getQueryLog()[1]['query']);

        DB::table('test_table')->insert(['test' => 1]);

        Schema::table('test_table', function (Blueprint $table) {
            $table->dropIndexIfExists('test_table_test_index');
        });

        $this->assertEquals("drop index \"test_table_test_index\"", DB::getQueryLog()[3]['query']);

        Schema::table('test_table', function (Blueprint $table) {
            $table->dropIndexIfExists('test_table_test_index');

            $this->assertFalse($table->hasIndex('test_table_test_index'));
        });

        $this->assertDatabaseHas('test_table', ['id' => 1]);
    }
}