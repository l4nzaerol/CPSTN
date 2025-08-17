import { useEffect, useState } from "react";
import { inventoryAPI, ordersAPI } from "../../services/api";

export default function ReportsPage() {
  const [lowStock, setLowStock] = useState({ raw_materials: [], products: [] });
  const [orders, setOrders] = useState([]);

  useEffect(() => {
    (async () => {
      try {
        const [ls, os] = await Promise.all([
          inventoryAPI.getLowStock(),
          ordersAPI.getAll({ per_page: 5 })
        ]);
        setLowStock(ls.data);
        setOrders(os.data.data || []);
      } catch (err) {
        console.error("Failed to fetch reports", err);
      }
    })();
  }, []);

  return (
    <div>
      <h2>📑 Reports</h2>

      <h3>Low Stock Summary</h3>
      <div style={{ display: "flex", gap: 20 }}>
        <div>
          <h4>Raw Materials</h4>
          <ul>
            {(lowStock.raw_materials || []).map((m) => (
              <li key={m.id}>
                {m.name} (Stock: {m.current_stock}, Min: {m.minimum_stock})
              </li>
            ))}
          </ul>
        </div>
        <div>
          <h4>Products</h4>
          <ul>
            {(lowStock.products || []).map((p) => (
              <li key={p.id}>
                {p.name} (Stock: {p.current_stock}, Min: {p.minimum_stock})
              </li>
            ))}
          </ul>
        </div>
      </div>

      <h3 style={{ marginTop: 24 }}>Recent Orders</h3>
      <ul>
        {orders.map((o) => (
          <li key={o.id}>
            #{o.order_number} - {o.status} - ₱{o.total_amount}
          </li>
        ))}
      </ul>
    </div>
  );
}
