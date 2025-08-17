import { useEffect, useState } from 'react';
import { productsAPI, ordersAPI, authAPI } from '../services/api';
import { useNavigate } from 'react-router-dom';

export default function CustomerPortal() {
	const navigate = useNavigate();
	const [products, setProducts] = useState([]);
	const [cart, setCart] = useState({});
	const [message, setMessage] = useState('');

	useEffect(() => {
		(async () => {
			try {
				await authAPI.profile();
				const res = await productsAPI.getAll({ per_page: 50 });
				setProducts(res.data.data || []);
			} catch (e) {
				navigate('/login', { replace: true });
			}
		})();
	}, [navigate]);

	const addToCart = (id) => setCart((c) => ({ ...c, [id]: (c[id] || 0) + 1 }));
	const removeFromCart = (id) => setCart((c) => { const n = { ...c }; delete n[id]; return n; });

	const placeOrder = async () => {
		setMessage('');
		const items = Object.entries(cart).map(([product_id, quantity]) => ({ product_id, quantity }));
		if (!items.length) return;
		try {
			const res = await ordersAPI.create({ items });
			setCart({});
			setMessage(`Order placed: #${res.data.order_number}`);
		} catch {
			setMessage('Failed to place order');
		}
	};

	return (
		<div style={{ padding: 20 }}>
			<h2>Customer Portal</h2>
			{message && <p>{message}</p>}
			<h3>Products</h3>
			<ul>
				{products.map((p) => (
					<li key={p.id}>
						{p.name} - {p.price}
						<button onClick={() => addToCart(p.id)} style={{ marginLeft: 8 }}>Add</button>
						{cart[p.id] && <button onClick={() => removeFromCart(p.id)} style={{ marginLeft: 8 }}>Remove</button>}
					</li>
				))}
			</ul>
			<button onClick={placeOrder} disabled={!Object.keys(cart).length}>Place Order</button>
		</div>
	);
}