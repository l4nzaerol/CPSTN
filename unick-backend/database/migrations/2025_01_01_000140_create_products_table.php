<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('products', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('sku')->unique();
			$table->text('description')->nullable();
			$table->decimal('price', 12, 2)->default(0);
			$table->integer('current_stock')->default(0);
			$table->integer('minimum_stock')->default(0);
			$table->string('category')->nullable();
			$table->json('specifications')->nullable();
			$table->string('image_url')->nullable();
			$table->string('status')->default('active');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('products');
	}
};