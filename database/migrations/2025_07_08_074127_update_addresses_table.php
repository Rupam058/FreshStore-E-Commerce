<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        // For SQLite, it's often easier to recreate the table

        // Step 1: Create new table with correct structure
        Schema::create('addresses_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->text('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->timestamps();
        });

        // Step 2: Copy data from old table to new table
        DB::statement('
            INSERT INTO addresses_new (id, user_id, order_id, first_name, last_name, phone, street_address, city, state, zip_code, created_at, updated_at)
            SELECT 
                a.id,
                o.user_id,
                a.order_id,
                a.first_name,
                a.last_name,
                a.phone,
                a.street_address,
                a.city,
                a.state,
                a.zip_code,
                a.created_at,
                a.updated_at
            FROM addresses a
            JOIN orders o 
            ON a.order_id = o.id
        ');

        // Step 3: Drop old table
        Schema::dropIfExists('addresses');

        // Step 4: Rename new table
        Schema::rename('addresses_new', 'addresses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // Create old structure
        Schema::create('addresses_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->text('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->timestamps();
        });

        // Copy data back
        DB::statement('
            INSERT INTO addresses_old (id, order_id, first_name, last_name, phone, street_address, city, state, zip_code, created_at, updated_at)
            SELECT id, order_id, first_name, last_name, phone, street_address, city, state, zip_code, created_at, updated_at
            FROM addresses
            WHERE order_id IS NOT NULL
        ');

        // Replace tables
        Schema::dropIfExists('addresses');
        Schema::rename('addresses_old', 'addresses');
    }
};
