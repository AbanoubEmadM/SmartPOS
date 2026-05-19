<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        if (Schema::hasColumn('sessions', 'employee_id') && ! Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->renameColumn('employee_id', 'user_id');
            });

            return;
        }

        if (! Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        if (Schema::hasColumn('sessions', 'user_id') && ! Schema::hasColumn('sessions', 'employee_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->renameColumn('user_id', 'employee_id');
            });
        }
    }
};
