<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('customers', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->string('company_name')->nullable();
			$table->string('tax_id')->nullable();
			$table->string('billing_address')->nullable();
			$table->string('shipping_address')->nullable();
			$table->timestamps();
			$table->unique('user_id');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('customers');
	}
};