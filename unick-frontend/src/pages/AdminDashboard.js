import { useEffect, useState } from 'react';
import { inventoryAPI, ordersAPI, authAPI } from '../services/api';
import { useNavigate } from 'react-router-dom';

export default function AdminDashboard() {
	const navigate = useNavigate();
	const [lowStock, setLowStock] = useState({ raw_materials: [], products: [] });
	const [orders, setOrders] = useState([]);

	useEffect(() => {
		(async () => {
			try {
				await authAPI.profile();
				const [ls, os] = await Promise.all([
					inventoryAPI.getLowStock(),
					ordersAPI.getAll({ per_page: 10 })
				]);
				setLowStock(ls.data);
				setOrders(os.data.data || []);
			} catch (e) {
				navigate('/login', { replace: true });
			}
		})();
	}, [navigate]);

	const logout = async () => {
		try { await authAPI.logout(); } catch {}
		localStorage.removeItem('auth_token');
		localStorage.removeItem('user_data');
		navigate('/login', { replace: true });
	};

	return (
		<div style={{ padding: 20 }}>
			<h2>Admin Dashboard</h2>
			<button onClick={logout} style={{ float: 'right' }}>Logout</button>
			<h3>Low Stock</h3>
			<div style={{ display: 'flex', gap: 20 }}>
				<div>
					<h4>Raw Materials</h4>
					<ul>
						{(lowStock.raw_materials || []).map((m) => (
							<li key={`rm-${m.id}`}>{m.name} (Stock: {m.current_stock}, Min: {m.minimum_stock})</li>
						))}
					</ul>
				</div>
				<div>
					<h4>Products</h4>
					<ul>
						{(lowStock.products || []).map((p) => (
							<li key={`p-${p.id}`}>{p.name} (Stock: {p.current_stock}, Min: {p.minimum_stock})</li>
						))}
					</ul>
				</div>
			</div>

			<h3 style={{ marginTop: 24 }}>Recent Orders</h3>
			<ul>
				{orders.map((o) => (
					<li key={o.id}>#{o.order_number} - {o.status} - {o.total_amount}</li>
				))}
			</ul>
		</div>
	);
}