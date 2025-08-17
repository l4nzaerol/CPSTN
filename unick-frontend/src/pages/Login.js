import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { authAPI } from '../services/api';
import '../styles/auth.css';

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
		<div className="auth-container">
			<div className="auth-card">
				<div className="auth-header">
					<div className="auth-logo">U</div>
					<h1 className="auth-title">Welcome Back</h1>
					<p className="auth-subtitle">Sign in to your account to continue</p>
				</div>
				
				<form onSubmit={onSubmit} className="auth-form">
					<div className="form-group">
						<label className="form-label" htmlFor="email">Email Address</label>
						<input
							type="email"
							id="email"
							className={`form-input ${error ? 'error' : ''}`}
							value={email}
							onChange={(e) => setEmail(e.target.value)}
							placeholder="Enter your email"
							required
						/>
					</div>
					
					<div className="form-group">
						<label className="form-label" htmlFor="password">Password</label>
						<input
							type="password"
							id="password"
							className={`form-input ${error ? 'error' : ''}`}
							value={password}
							onChange={(e) => setPassword(e.target.value)}
							placeholder="Enter your password"
							required
						/>
					</div>
					
					{error && (
						<div className="alert alert-error">
							{error}
						</div>
					)}
					
					<button 
						type="submit" 
						className={`btn btn-primary ${loading ? 'btn-loading' : ''}`}
						disabled={loading}
					>
						{loading ? 'Signing in...' : 'Sign In'}
					</button>
				</form>
				
				<div className="auth-footer">
					<p>
						Don't have an account?{' '}
						<Link to="/register" className="auth-link">
							Create one now
						</Link>
					</p>
				</div>
			</div>
		</div>
	);
}
