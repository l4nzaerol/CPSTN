<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'role')) {
            $table->string('role')->default('customer')->after('password');
        }
        if (!Schema::hasColumn('users', 'phone')) {
            $table->string('phone')->nullable()->after('role');
        }
        if (!Schema::hasColumn('users', 'address')) {
            $table->string('address')->nullable()->after('phone');
        }
    });
}


	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['role', 'phone', 'address']);
		});
	}
};