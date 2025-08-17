import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/Login';
import Register from './pages/Register';
import AdminDashboard from './pages/AdminDashboard';
import CustomerPortal from './pages/CustomerPortal';

function PrivateRoute({ children }) {
	const token = localStorage.getItem('auth_token');
	return token ? children : <Navigate to="/login" replace />;
}

function App() {
	return (
		<BrowserRouter>
			<Routes>
				<Route path="/login" element={<Login />} />
				<Route path="/register" element={<Register />} />
				<Route path="/admin" element={<PrivateRoute><AdminDashboard /></PrivateRoute>} />
				<Route path="/portal" element={<PrivateRoute><CustomerPortal /></PrivateRoute>} />
				<Route path="*" element={<Navigate to="/login" replace />} />
			</Routes>
		</BrowserRouter>
	);
}

export default App;
