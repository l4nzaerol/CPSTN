<html>
	<head>
		<meta charset="utf-8">
		<title>Sales Report</title>
		<style>
			body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
			table { width: 100%; border-collapse: collapse; margin-top: 16px; }
			th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
			th { background: #f5f5f5; }
		</style>
	</head>
	<body>
		<h2>Sales Report</h2>
		<p>Date: {{ now()->toDateString() }}</p>
		<h3>Summary</h3>
		<ul>
			<li>Total Sales: {{ number_format($data->summary->total_sales ?? 0, 2) }}</li>
			<li>Total Orders: {{ $data->summary->total_orders ?? 0 }}</li>
			<li>Average Order Value: {{ number_format($data->summary->average_order_value ?? 0, 2) }}</li>
		</ul>
		<h3>Top Products</h3>
		<table>
			<thead>
				<tr>
					<th>Product</th>
					<th>Total Quantity</th>
					<th>Total Sales</th>
				</tr>
			</thead>
			<tbody>
				@foreach(($data->summary->top_products ?? []) as $p)
					<tr>
						<td>{{ $p->name ?? '' }}</td>
						<td>{{ $p->total_quantity ?? 0 }}</td>
						<td>{{ number_format($p->total_sales ?? 0, 2) }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</body>
</html>