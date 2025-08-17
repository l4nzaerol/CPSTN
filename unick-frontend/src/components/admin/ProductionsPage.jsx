import { useEffect, useState } from "react";
import { productionsAPI } from "../../services/api";

export default function ProductionsPage() {
  const [productions, setProductions] = useState([]);

  useEffect(() => {
    (async () => {
      try {
        const { data } = await productionsAPI.getAll();
        setProductions(data);
      } catch (err) {
        console.error("Failed to fetch productions", err);
      }
    })();
  }, []);

  return (
    <div>
      <h2>🏭 Productions</h2>
      <table className="table">
        <thead>
          <tr>
            <th>Batch #</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Date Started</th>
          </tr>
        </thead>
        <tbody>
          {productions.map((p) => (
            <tr key={p.id}>
              <td>{p.batch_number}</td>
              <td>{p.product?.name}</td>
              <td>{p.quantity}</td>
              <td>{p.status}</td>
              <td>{new Date(p.start_date).toLocaleDateString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
