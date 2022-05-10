<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use IndexHints\Hintable;

/**
 * @method static Builder|Model forceIndex(array|string[] $indexes, string $for = '', string $as = '')
 * @method static Builder|Model useIndex(string|string[] $indexes, string $for = '', string $as = '')
 * @method static Builder|Model ignoreIndex(array|string[] $indexes, string $for = '', string $as = '')
 */
class ExampleModel extends Model
{
    use Hintable;

    protected $fillable = ['test'];
}