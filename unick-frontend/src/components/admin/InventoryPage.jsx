import { useEffect, useState } from "react";
import { inventoryAPI } from "../../services/api";

export default function InventoryPage() {
  const [inventory, setInventory] = useState([]);

  useEffect(() => {
    (async () => {
      try {
        const { data } = await inventoryAPI.getAll();
        setInventory(data);
      } catch (err) {
        console.error("Failed to fetch inventory", err);
      }
    })();
  }, []);

  return (
    <div>
      <h2>📊 Inventory</h2>
      <table className="table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Current Stock</th>
            <th>Minimum Stock</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          {inventory.map((i) => (
            <tr key={i.id}>
              <td>{i.name}</td>
              <td>{i.current_stock}</td>
              <td>{i.minimum_stock}</td>
              <td>{i.current_stock <= i.minimum_stock ? "⚠ Low Stock" : "✅ OK"}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
