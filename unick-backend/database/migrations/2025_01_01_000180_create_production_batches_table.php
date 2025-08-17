<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('production_batches', function (Blueprint $table) {
			$table->id();
			$table->string('batch_number')->unique();
			$table->foreignId('order_id')->constrained()->cascadeOnDelete();
			$table->string('status')->default('planned');
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();
			$table->text('notes')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('production_batches');
	}
};