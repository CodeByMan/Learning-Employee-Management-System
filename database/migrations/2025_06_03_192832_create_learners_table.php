<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learners', function (Blueprint $table): void {
            $table->id();
            $table->string('fname', 100);
            $table->string('mname', 100)->nullable();
            $table->string('lname', 100);
            $table->string('email')->unique();
            $table->string('grade_level', 50);
            $table->string('section', 20);
            $table->timestamps();

            $table->index(['grade_level', 'section']);
            $table->index(['lname', 'fname']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learners');
    }
};
