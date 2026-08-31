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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('serial_number', 100)->nullable();
            $table->boolean('has_no_serial_number')->default(false);
            $table->string('brand', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('photo_path')->nullable();
            $table->enum('condition', ['Baik', 'Rusak'])->default('Baik');

            // Foreign Keys for Classification & Location
            $table->foreignUuid('category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $table->foreignUuid('building_id')->nullable()->constrained('buildings')->nullOnDelete();
            $table->foreignUuid('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignUuid('function_id')->nullable()->constrained('item_functions')->nullOnDelete();

            // Admin Audit Tracking
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
