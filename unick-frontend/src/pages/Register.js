import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { authAPI } from '../services/api';

export default function Register() {
	const navigate = useNavigate();
	const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
	const [error, setError] = useState('');
	const [loading, setLoading] = useState(false);

	const update = (e) => setForm({ ...form, [e.target.name]: e.target.value });

	const onSubmit = async (e) => {
		e.preventDefault();
		setError('');
		setLoading(true);
		try {
			const { data } = await authAPI.register(form);
			localStorage.setItem('auth_token', data.token);
			localStorage.setItem('user_data', JSON.stringify(data.user));
			navigate('/portal', { replace: true });
		} catch (err) {
			setError(err.response?.data?.message || 'Register failed');
		} finally {
			setLoading(false);
		}
	};

	return (
		<div style={{ maxWidth: 420, margin: '60px auto' }}>
			<h2>Register</h2>
			<form onSubmit={onSubmit}>
				<div>
					<label>Name</label>
					<input name="name" value={form.name} onChange={update} required />
				</div>
				<div>
					<label>Email</label>
					<input type="email" name="email" value={form.email} onChange={update} required />
				</div>
				<div>
					<label>Password</label>
					<input type="password" name="password" value={form.password} onChange={update} required />
				</div>
				<div>
					<label>Confirm Password</label>
					<input type="password" name="password_confirmation" value={form.password_confirmation} onChange={update} required />
				</div>
				{error && <p style={{ color: 'red' }}>{error}</p>}
				<button type="submit" disabled={loading} style={{ marginTop: 12 }}>{loading ? 'Registering...' : 'Register'}</button>
			</form>
		</div>
	);
}