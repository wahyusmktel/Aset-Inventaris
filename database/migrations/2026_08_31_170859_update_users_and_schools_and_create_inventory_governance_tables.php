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
        // 1. Update users table with role, nip, phone, is_active
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'anggota'])->default('anggota')->after('email');
            $table->string('nip', 50)->nullable()->after('role');
            $table->string('phone', 30)->nullable()->after('nip');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        // 2. Update schools table with principal_nip, kaur_it_name, kaur_it_nip
        Schema::table('schools', function (Blueprint $table) {
            $table->string('principal_nip', 50)->nullable()->after('principal_name');
            $table->string('kaur_it_name', 255)->nullable()->after('principal_nip');
            $table->string('kaur_it_nip', 50)->nullable()->after('kaur_it_name');
        });

        // 3. Inventory Periods & Cutoff Settings
        Schema::create('inventory_periods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->dateTime('start_date');
            $table->dateTime('cutoff_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Integrity Pacts (Pakta Integritas)
        Schema::create('integrity_pacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('document_number', 100)->unique();
            $table->boolean('is_agreed')->default(true);
            $table->dateTime('signed_at');
            $table->string('signer_ip', 50)->nullable();
            $table->string('digital_signature_hash', 100);
            $table->string('pdf_path', 255)->nullable();
            $table->timestamps();
        });

        // 5. Data Finalizations (Berita Acara Finalisasi)
        Schema::create('data_finalizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('inventory_period_id')->nullable()->constrained('inventory_periods')->nullOnDelete();
            $table->string('document_number', 100)->unique();
            $table->integer('total_items_recorded')->default(0);
            $table->integer('total_units_recorded')->default(0);
            $table->integer('total_good_condition')->default(0);
            $table->integer('total_damaged_condition')->default(0);
            $table->text('statement_notes')->nullable();
            $table->dateTime('signed_at');
            $table->boolean('is_finalized')->default(true);
            $table->string('pdf_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_finalizations');
        Schema::dropIfExists('integrity_pacts');
        Schema::dropIfExists('inventory_periods');

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['principal_nip', 'kaur_it_name', 'kaur_it_nip']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nip', 'phone', 'is_active']);
        });
    }
};
