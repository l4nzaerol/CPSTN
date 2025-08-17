import { useEffect, useState } from "react";
import { ordersAPI } from "../../services/api";


export default function OrdersPage() {
  const [orders, setOrders] = useState([]);

  useEffect(() => {
    (async () => {
      try {
        const { data } = await ordersAPI.getAll();
        setOrders(data.data || []);
      } catch (err) {
        console.error("Failed to fetch orders", err);
      }
    })();
  }, []);

  return (
    <div>
      <h2>🛒 Orders</h2>
      <table className="table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          {orders.map((o) => (
            <tr key={o.id}>
              <td>#{o.order_number}</td>
              <td>{o.status}</td>
              <td>₱{o.total_amount}</td>
              <td>{new Date(o.created_at).toLocaleDateString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
