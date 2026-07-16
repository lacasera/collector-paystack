<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('rolls the collector user columns up and down cleanly on sqlite', function () {
    // Regression: down() dropped the indexed `paystack_id` column directly,
    // which SQLite refuses ("error in index ... after drop column"). It must
    // drop the index first. Exercise a fresh up()/down() cycle to prove it.
    Schema::dropIfExists('users');
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->rememberToken();
        $table->timestamps();
    });

    $migration = include __DIR__ . '/../../database/migrations/2023_06_13_234144_add_collector_columns_to_user_table.php';

    $migration->up();
    expect(Schema::hasColumn('users', 'paystack_id'))->toBeTrue();

    $migration->down();
    expect(Schema::hasColumn('users', 'paystack_id'))->toBeFalse();
});
