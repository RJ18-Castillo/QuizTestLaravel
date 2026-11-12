<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 | Base de datos limpia por test (SQLite en memoria según phpunit.xml).
 */
pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
