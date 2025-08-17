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

  const addToCart = (id) =>
    setCart((c) => ({ ...c, [id]: (c[id] || 0) + 1 }));

  const removeFromCart = (id) =>
    setCart((c) => {
      const n = { ...c };
      if (n[id] > 1) n[id] -= 1;
      else delete n[id];
      return n;
    });

  const placeOrder = async () => {
    setMessage('');
    const items = Object.entries(cart).map(([product_id, quantity]) => ({
      product_id,
      quantity,
    }));
    if (!items.length) return;
    try {
      const res = await ordersAPI.create({ items });
      setCart({});
      setMessage(`✅ Order placed: #${res.data.order_number}`);
    } catch {
      setMessage('❌ Failed to place order');
    }
  };

  const handleLogout = async () => {
    try {
      await authAPI.logout();
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_data');
      navigate('/login', { replace: true });
    } catch {
      console.error('Logout failed');
    }
  };

  return (
    <div style={{ display: 'flex', minHeight: '100vh' }}>
      {/* Sidebar for Cart */}
      <div style={{ width: '25%', padding: 20, borderRight: '1px solid #ddd' }}>
        <h2>🛒 My Cart</h2>
        {Object.keys(cart).length === 0 && <p>No items yet</p>}
        <ul>
          {Object.entries(cart).map(([id, qty]) => {
            const product = products.find((p) => p.id === parseInt(id));
            return (
              <li key={id} style={{ marginBottom: 8 }}>
                {product?.name} × {qty}
                <button
                  onClick={() => removeFromCart(id)}
                  style={{ marginLeft: 8 }}
                >
                  −
                </button>
              </li>
            );
          })}
        </ul>
        <button
          onClick={placeOrder}
          disabled={!Object.keys(cart).length}
          style={{
            marginTop: 12,
            padding: '10px 16px',
            background: '#28a745',
            color: 'white',
            border: 'none',
            borderRadius: 5,
            cursor: 'pointer',
          }}
        >
          Place Order
        </button>
        {message && <p style={{ marginTop: 10 }}>{message}</p>}
      </div>

      {/* Main Product Display */}
      <div style={{ flex: 1, padding: 20 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <h2>🛍️ Customer Portal</h2>
          <button
            onClick={handleLogout}
            style={{
              padding: '8px 14px',
              background: '#dc3545',
              color: 'white',
              border: 'none',
              borderRadius: 5,
              cursor: 'pointer',
            }}
          >
            Logout
          </button>
        </div>

        <h3 style={{ marginTop: 20 }}>Available Products</h3>
        <div
          style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))',
            gap: 16,
            marginTop: 12,
          }}
        >
          {products.map((p) => (
            <div
              key={p.id}
              style={{
                border: '1px solid #ddd',
                borderRadius: 10,
                padding: 16,
                background: 'white',
                boxShadow: '0 2px 5px rgba(0,0,0,0.05)',
              }}
            >
              <h4>{p.name}</h4>
              <p style={{ color: '#555' }}>₱{p.price}</p>
              <button
                onClick={() => addToCart(p.id)}
                style={{
                  padding: '8px 12px',
                  background: '#007bff',
                  color: 'white',
                  border: 'none',
                  borderRadius: 5,
                  cursor: 'pointer',
                }}
              >
                Add to Cart
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
