import { useEffect, useState } from "react";
import { productsAPI } from "../../services/api";


export default function ProductsPage() {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    (async () => {
      try {
        const { data } = await productsAPI.getAll();
        setProducts(data);
      } catch (err) {
        console.error("Failed to fetch products", err);
      }
    })();
  }, []);

  return (
    <div>
      <h2>📦 Products</h2>
      <table className="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
          </tr>
        </thead>
        <tbody>
          {products.map((p) => (
            <tr key={p.id}>
              <td>{p.name}</td>
              <td>{p.category}</td>
              <td>₱{p.price}</td>
              <td>{p.stock}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
