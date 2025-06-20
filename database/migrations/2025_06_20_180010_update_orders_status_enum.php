<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the existing enum column and recreate it with new values
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add the updated status enum column
            $table->enum('status', ['processing', 'confirmed', 'shipping', 'shipped', 'delivered', 'cancelled', 'refunded'])
                ->default('processing')
                ->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the updated enum column
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Restore the original status enum column
            $table->enum('status', ['new', 'processing', 'completed', 'cancelled', 'refunded'])
                ->default('new')
                ->after('payment_status');
        });
    }
};
