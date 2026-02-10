<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('total_ventes', 15, 2)->default(0);
            $table->decimal('total_depenses', 15, 2)->default(0);
            $table->decimal('benefice_net', 15, 2)->default(0);
            $table->integer('nombre_ventes')->default(0);
            $table->integer('nombre_depenses')->default(0);
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['boutique_id', 'date']);
            $table->index('boutique_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
