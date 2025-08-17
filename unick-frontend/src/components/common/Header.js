import { Link, useNavigate } from 'react-router-dom';

export default function Header() {
	const navigate = useNavigate();
	const logout = () => {
		localStorage.removeItem('auth_token');
		localStorage.removeItem('user_data');
		navigate('/login');
	};
	return (
		<div style={{ display: 'flex', gap: 12, padding: 12, background: '#f9f9f9' }}>
			<Link to="/admin">Admin</Link>
			<Link to="/portal">Portal</Link>
			<button onClick={logout}>Logout</button>
		</div>
	);
}