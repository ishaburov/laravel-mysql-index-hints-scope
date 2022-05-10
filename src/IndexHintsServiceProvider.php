<?php

namespace IndexHints;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;

/**
 * @see Blueprint
 * @method static Blueprint|Fluent dropIndexIfExists($index);
 * @method static Blueprint|bool hasIndex($index);
 * @method static Blueprint|Fluent dropIndex($index);
 * @method static Blueprint|string getTable();
 */
class IndexHintsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerSchemaMacros();
    }

    /**
     * Register the schema macros.
     */
    protected function registerSchemaMacros(): void
    {
        Blueprint::macro('dropIndexIfExists', function (string $index): Fluent {

            if ($this->hasIndex($index)) {
                return $this->dropIndex($index);
            }

            return new Fluent();
        });

        Blueprint::macro('hasIndex', function (string $index): bool {
            $conn = Schema::getConnection();
            $dbSchemaManager = $conn->getDoctrineSchemaManager();

            $doctrineTable = $dbSchemaManager->listTableDetails($this->getTable());

            return $doctrineTable->hasIndex($index);
        });
    }
}
