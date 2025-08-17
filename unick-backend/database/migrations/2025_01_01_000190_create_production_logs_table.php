<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('production_logs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
			$table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
			$table->string('stage');
			$table->decimal('hours_worked', 8, 2)->default(0);
			$table->integer('quantity_completed')->nullable();
			$table->text('notes')->nullable();
			$table->date('log_date');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('production_logs');
	}
};