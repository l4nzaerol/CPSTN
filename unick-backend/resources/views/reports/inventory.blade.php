<html>
	<head>
		<meta charset="utf-8">
		<title>Inventory Report</title>
		<style>
			body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
			table { width: 100%; border-collapse: collapse; margin-top: 16px; }
			th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
			th { background: #f5f5f5; }
		</style>
	</head>
	<body>
		<h2>Inventory Report</h2>
		<p>Date: {{ now()->toDateString() }}</p>
		<h3>Summary</h3>
		<ul>
			<li>Total Raw Materials: {{ $data->summary->total_raw_materials ?? 0 }}</li>
			<li>Total Products: {{ $data->summary->total_products ?? 0 }}</li>
			<li>Low Stock Materials: {{ $data->summary->low_stock_materials ?? 0 }}</li>
			<li>Low Stock Products: {{ $data->summary->low_stock_products ?? 0 }}</li>
			<li>Total Inventory Value: {{ number_format($data->summary->total_inventory_value ?? 0, 2) }}</li>
		</ul>
		<h3>Raw Materials</h3>
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>SKU</th>
					<th>Stock</th>
					<th>Unit Cost</th>
					<th>Supplier</th>
				</tr>
			</thead>
			<tbody>
				@foreach(($data->raw_materials ?? []) as $rm)
					<tr>
						<td>{{ $rm->name ?? '' }}</td>
						<td>{{ $rm->sku ?? '' }}</td>
						<td>{{ $rm->current_stock ?? 0 }}</td>
						<td>{{ number_format($rm->unit_cost ?? 0, 2) }}</td>
						<td>{{ $rm->supplier->name ?? '' }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</body>
</html>