<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('raw_materials', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('sku')->unique();
			$table->text('description')->nullable();
			$table->string('unit')->nullable();
			$table->decimal('unit_cost', 12, 2)->default(0);
			$table->integer('current_stock')->default(0);
			$table->integer('minimum_stock')->default(0);
			$table->integer('maximum_stock')->default(0);
			$table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('raw_materials');
	}
};