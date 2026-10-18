<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('service_id')->constrained()->cascadeOnDelete();
            $t->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status')->default('submitted');
            $t->string('urgency')->default('normal');
            $t->json('data')->nullable();
            $t->text('remarks')->nullable();
            $t->decimal('fee_amount', 10, 2)->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->string('certificate_code')->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('application_files', function (Blueprint $t) {
            $t->id();
            $t->foreignId('application_id')->constrained()->cascadeOnDelete();
            $t->string('label');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime')->nullable();
            $t->unsignedBigInteger('size')->default(0);
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('application_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->string('from_status')->nullable();
            $t->string('to_status')->nullable();
            $t->text('comment')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('application_files');
        Schema::dropIfExists('applications');
    }
};
