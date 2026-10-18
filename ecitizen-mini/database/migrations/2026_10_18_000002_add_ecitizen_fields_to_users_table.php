<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('citizen')->after('password'); // citizen | officer | admin
            $t->string('national_id')->nullable()->unique()->after('email');
            $t->string('phone')->nullable();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('otp_code')->nullable();
            $t->timestamp('otp_expires_at')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('department_id');
            $t->dropColumn(['role', 'national_id', 'phone', 'otp_code', 'otp_expires_at']);
        });
    }
};
