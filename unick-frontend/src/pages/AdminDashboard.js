import { useEffect, useState } from 'react';
import { authAPI } from '../services/api';
import { useNavigate, Routes, Route, Link } from 'react-router-dom';
import ProductsPage from '../components/admin/ProductsPage';
import OrdersPage from '../components/admin/OrdersPage';
import ProductionsPage from '../components/admin/ProductionsPage';
import InventoryPage from '../components/admin/InventoryPage';
import ReportsPage from '../components/admin/ReportsPage';
import '../pages/AdminDashboard.css';

export default function AdminDashboard() {
	const navigate = useNavigate();
	const [user, setUser] = useState(null);

	useEffect(() => {
		(async () => {
			try {
				const token = localStorage.getItem('auth_token');
				if (!token) return navigate('/login', { replace: true });

				const { data } = await authAPI.profile(token); // ✅ pass token
				setUser(data.user || data);
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
		<div className="admin-container">
			{/* Sidebar */}
			<aside className="sidebar">
				<h2 className="sidebar-title">Admin Panel</h2>
				<nav className="sidebar-nav">
					<Link to="products">Products</Link>
					<Link to="orders">Orders</Link>
					<Link to="productions">Productions</Link>
					<Link to="inventory">Inventory</Link>
					<Link to="reports">Reports</Link>
				</nav>
				<button onClick={logout} className="logout-btn">Logout</button>
			</aside>

			{/* Main Content */}
			<main className="dashboard-content">
				<Routes>
					<Route path="products" element={<ProductsPage />} />
					<Route path="orders" element={<OrdersPage />} />
					<Route path="productions" element={<ProductionsPage />} />
					<Route path="inventory" element={<InventoryPage />} />
					<Route path="reports" element={<ReportsPage />} />
					<Route path="*" element={<h2>Welcome, {user?.name || "Admin"} 👋</h2>} />
				</Routes>
			</main>
		</div>
	);
}
