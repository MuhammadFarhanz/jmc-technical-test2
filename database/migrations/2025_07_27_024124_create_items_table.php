<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Informasi Umum
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('kategori_id')->constrained('kategori');
            $table->foreignId('sub_kategori_id')->constrained('sub_kategori');
            $table->decimal('batas_harga', 15, 2);
            $table->string('nomor_surat', 200)->unique();
            $table->string('asal_barang', 200);
            $table->string('lampiran_path')->nullable();

            // Informasi Barang (disimpan sebagai JSON)
            $table->json('daftar_barang'); // Menyimpan array barang

            // Summary
            $table->integer('total_item');
            $table->decimal('total_harga', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
