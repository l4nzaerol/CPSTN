<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('inventory_transactions', function (Blueprint $table) {
			$table->id();
			$table->enum('type', ['in', 'out', 'adjustment']);
			$table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
			$table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
			$table->integer('quantity');
			$table->decimal('unit_cost', 12, 2)->nullable();
			$table->string('reference')->nullable();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('inventory_transactions');
	}
};