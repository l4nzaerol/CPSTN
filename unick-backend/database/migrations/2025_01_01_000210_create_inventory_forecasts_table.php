<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('inventory_forecasts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
			$table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
			$table->date('forecast_date');
			$table->decimal('predicted_consumption', 12, 2)->nullable();
			$table->decimal('predicted_demand', 12, 2)->nullable();
			$table->decimal('recommended_reorder_quantity', 12, 2)->nullable();
			$table->text('analysis_notes')->nullable();
			$table->timestamps();
			$table->unique(['raw_material_id', 'product_id', 'forecast_date']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('inventory_forecasts');
	}
};