<html>
	<head>
		<meta charset="utf-8">
		<title>Production Report</title>
		<style>
			body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
			table { width: 100%; border-collapse: collapse; margin-top: 16px; }
			th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
			th { background: #f5f5f5; }
		</style>
	</head>
	<body>
		<h2>Production Report</h2>
		<p>Date: {{ now()->toDateString() }}</p>
		<h3>Summary</h3>
		<ul>
			<li>Total Batches: {{ $data->summary->total_batches ?? 0 }}</li>
			<li>Completed Batches: {{ $data->summary->completed_batches ?? 0 }}</li>
			<li>In Progress Batches: {{ $data->summary->in_progress_batches ?? 0 }}</li>
			<li>Completion Rate: {{ number_format($data->summary->completion_rate ?? 0, 2) }}%</li>
		</ul>
		<h3>Batches</h3>
		<table>
			<thead>
				<tr>
					<th>Batch #</th>
					<th>Status</th>
					<th>Order</th>
					<th>Assigned Staff</th>
					<th>Start Date</th>
					<th>End Date</th>
				</tr>
			</thead>
			<tbody>
				@foreach(($data->batches ?? []) as $b)
					<tr>
						<td>{{ $b->batch_number ?? '' }}</td>
						<td>{{ $b->status ?? '' }}</td>
						<td>{{ $b->order->order_number ?? '' }}</td>
						<td>{{ $b->assigned_staff->name ?? '' }}</td>
						<td>{{ $b->start_date ?? '' }}</td>
						<td>{{ $b->end_date ?? '' }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</body>
</html>