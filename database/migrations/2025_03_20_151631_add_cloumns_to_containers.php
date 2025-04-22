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
        Schema::table('containers', function (Blueprint $table) {
            //
            $table->boolean('is_delevired')->default(0)->after('file_path');
            $table->date('delivery_date')->nullable()->after('is_delevired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('containers', function (Blueprint $table) {
            //
            $table->dropColumn('is_delevired');
            $table->dropColumn('delivery_date');
        });
    }
};
