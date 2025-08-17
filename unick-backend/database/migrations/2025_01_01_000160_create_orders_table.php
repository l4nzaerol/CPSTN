<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('orders', function (Blueprint $table) {
			$table->id();
			$table->string('order_number')->unique();
			$table->foreignId('customer_id')->constrained()->cascadeOnDelete();
			$table->decimal('subtotal', 12, 2)->default(0);
			$table->decimal('tax_amount', 12, 2)->default(0);
			$table->decimal('total_amount', 12, 2)->default(0);
			$table->string('status')->default('pending');
			$table->text('notes')->nullable();
			$table->date('order_date');
			$table->date('delivery_date')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('orders');
	}
};