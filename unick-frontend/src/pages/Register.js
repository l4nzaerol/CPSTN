import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { authAPI } from '../services/api';
import '../styles/auth.css';

export default function Register() {
	const navigate = useNavigate();
	const [form, setForm] = useState({ 
		name: '', 
		email: '', 
		password: '', 
		password_confirmation: '' 
	});
	const [error, setError] = useState('');
	const [loading, setLoading] = useState(false);
	const [passwordStrength, setPasswordStrength] = useState('');

	const update = (e) => {
		const { name, value } = e.target;
		setForm({ ...form, [name]: value });
		
		if (name === 'password') {
			checkPasswordStrength(value);
		}
	};

	const checkPasswordStrength = (password) => {
		if (password.length === 0) {
			setPasswordStrength('');
		} else if (password.length < 6) {
			setPasswordStrength('weak');
		} else if (password.length < 8) {
			setPasswordStrength('medium');
		} else {
			setPasswordStrength('strong');
		}
	};

	const onSubmit = async (e) => {
		e.preventDefault();
		setError('');
		
		if (form.password !== form.password_confirmation) {
			setError('Passwords do not match');
			return;
		}
		
		setLoading(true);
		try {
			const { data } = await authAPI.register(form);
			localStorage.setItem('auth_token', data.token);
			localStorage.setItem('user_data', JSON.stringify(data.user));
			navigate('/portal', { replace: true });
		} catch (err) {
			setError(err.response?.data?.message || 'Registration failed');
		} finally {
			setLoading(false);
		}
	};

	const getPasswordStrengthClass = () => {
		switch (passwordStrength) {
			case 'weak': return 'password-strength-weak';
			case 'medium': return 'password-strength-medium';
			case 'strong': return 'password-strength-strong';
			default: return '';
		}
	};

	const getPasswordStrengthText = () => {
		switch (passwordStrength) {
			case 'weak': return 'Weak password';
			case 'medium': return 'Medium strength';
			case 'strong': return 'Strong password';
			default: return '';
		}
	};

	return (
		<div className="auth-container">
			<div className="auth-card">
				<div className="auth-header">
					<div className="auth-logo">U</div>
					<h1 className="auth-title">Create Account</h1>
					<p className="auth-subtitle">Join us and start your journey</p>
				</div>
				
				<form onSubmit={onSubmit} className="auth-form">
					<div className="form-group">
						<label className="form-label" htmlFor="name">Full Name</label>
						<input
							type="text"
							id="name"
							name="name"
							className={`form-input ${error ? 'error' : ''}`}
							value={form.name}
							onChange={update}
							placeholder="Enter your full name"
							required
						/>
					</div>
					
					<div className="form-group">
						<label className="form-label" htmlFor="email">Email Address</label>
						<input
							type="email"
							id="email"
							name="email"
							className={`form-input ${error ? 'error' : ''}`}
							value={form.email}
							onChange={update}
							placeholder="Enter your email"
							required
						/>
					</div>
					
					<div className="form-group">
						<label className="form-label" htmlFor="password">Password</label>
						<input
							type="password"
							id="password"
							name="password"
							className={`form-input ${error ? 'error' : ''}`}
							value={form.password}
							onChange={update}
							placeholder="Create a password"
							required
						/>
						{passwordStrength && (
							<div className="password-strength">
								<div className="password-strength-bar">
									<div className={`password-strength-fill ${getPasswordStrengthClass()}`}></div>
								</div>
								<div className="password-strength-text">{getPasswordStrengthText()}</div>
							</div>
						)}
					</div>
					
					<div className="form-group">
						<label className="form-label" htmlFor="password_confirmation">Confirm Password</label>
						<input
							type="password"
							id="password_confirmation"
							name="password_confirmation"
							className={`form-input ${error ? 'error' : ''}`}
							value={form.password_confirmation}
							onChange={update}
							placeholder="Confirm your password"
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
						{loading ? 'Creating Account...' : 'Create Account'}
					</button>
				</form>
				
				<div className="auth-footer">
					<p>
						Already have an account?{' '}
						<Link to="/login" className="auth-link">
							Sign in here
						</Link>
					</p>
				</div>
			</div>
		</div>
	);
}
