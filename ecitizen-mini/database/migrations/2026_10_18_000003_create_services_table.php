<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('category');
            $t->text('description')->nullable();
            $t->decimal('fee', 10, 2)->default(0);
            $t->unsignedInteger('sla_days')->default(5);
            $t->json('fields')->nullable();              // dynamic form definition
            $t->json('required_documents')->nullable();  // list of upload labels
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('services'); }
};
