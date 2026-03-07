<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // 1. Buat kolom secara eksplisit sebagai INT (sama dengan id di tabel peminjaman)
            $table->unsignedInteger('peminjaman_id');
            $table->foreign('peminjaman_id')->references('id')->on('peminjaman')->onDelete('cascade');
            
            // 2. Buat kolom secara eksplisit sebagai INT (karena id di tabel users juga integer)
            $table->unsignedInteger('sender_id');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            
            // Isi pesan chat
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
