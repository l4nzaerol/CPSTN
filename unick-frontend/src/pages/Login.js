import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { authAPI } from '../services/api';

export default function Login() {
	const navigate = useNavigate();
	const [email, setEmail] = useState('');
	const [password, setPassword] = useState('');
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState('');

	const onSubmit = async (e) => {
		e.preventDefault();
		setError('');
		setLoading(true);
		try {
			const { data } = await authAPI.login({ email, password });
			localStorage.setItem('auth_token', data.token);
			localStorage.setItem('user_data', JSON.stringify(data.user));
			if (data.user.role === 'admin' || data.user.role === 'staff') {
				navigate('/admin', { replace: true });
			} else {
				navigate('/portal', { replace: true });
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Login failed');
		} finally {
			setLoading(false);
		}
	};

	return (
		<div style={{ maxWidth: 360, margin: '60px auto' }}>
			<h2>Login</h2>
			<form onSubmit={onSubmit}>
				<div>
					<label>Email</label>
					<input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
				</div>
				<div style={{ marginTop: 8 }}>
					<label>Password</label>
					<input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
				</div>
				{error && <p style={{ color: 'red' }}>{error}</p>}
				<button type="submit" disabled={loading} style={{ marginTop: 12 }}>{loading ? 'Signing in...' : 'Login'}</button>
			</form>
		</div>
	);
}