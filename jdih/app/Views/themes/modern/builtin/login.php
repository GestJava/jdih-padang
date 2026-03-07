<?= $this->extend('themes/modern/register/layout') ?>
<?= $this->section('content') ?>
<?php // Use register/layout.php 
?>
<div class="card-header transparent-header">
	<div class="logo">
		<img src="<?php echo $config->baseURL . '/images/' . $settingAplikasi['logo_login'] ?>">
	</div>

	<?php if (!empty($desc)) {
		echo '<p>' . esc($desc) . '</p>';
	} ?>
</div>
<div class="card-body">
	<?php

	// Tampilkan pesan sesuai status (success atau error)
	if (!empty($message)) {
		$alertClass = ($status === 'success') ? 'alert-success' : 'alert-danger';
		?>
		<div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
			<?= esc($message) ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	<?php }
	//echo password_hash('admin', PASSWORD_DEFAULT);
	?>
	<form method="post" action="<?= $config->baseURL ?>login" class="form-horizontal form-login">
		<div class="input-group mb-3">
			<div class="input-group-prepend login-input">
				<span class="input-group-text mt-1">
					<i class="fa fa-user"></i>
				</span>
			</div>
			<input type="text" name="username" value="<?= esc(@$_POST['username'] ?? '', 'attr') ?>" class="form-control login-input" placeholder="Username" aria-label="Username" required>
		</div>
		<div class="input-group mb-3">
			<div class="input-group-prepend login-input">
				<span class="input-group-text mt-1">
					<i class="fa fa-lock"></i>
				</span>
			</div>
			<input type="password" name="password" class="form-control login-input" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" required>
		</div>
		<div class="form-check">
			<input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberme">
			<label class="form-check-label" for="rememberme" style="font-weight:normal">Remember me</label>
		</div>
		
		<!-- Math CAPTCHA Verification (MFA) -->
		<div class="math-captcha-container mb-3 mt-3" id="mathCaptchaContainer">
			<div class="captcha-label mb-2">
				<i class="fa fa-shield-alt"></i> Verifikasi Keamanan: Jawab pertanyaan matematika berikut
			</div>
			<div class="captcha-wrapper">
				<div class="captcha-question">
					<span class="question-text"><?= esc($math_captcha['question'] ?? '') ?> = ?</span>
				</div>
				<div class="captcha-input-wrapper">
					<input type="number" 
						name="math_captcha_answer" 
						id="mathCaptchaAnswer" 
						class="form-control captcha-input" 
						placeholder="Masukkan jawaban" 
						required 
						min="0" 
						max="100"
						autocomplete="off">
					<input type="hidden" name="math_captcha_token" value="<?= esc($math_captcha['token'] ?? '') ?>">
				</div>
				<div class="captcha-hint">
					<small class="text-muted">
						<i class="fa fa-info-circle"></i> Jawab dengan angka saja
					</small>
				</div>
			</div>
		</div>
		
		<div class="mb-2 mt-3">
			<button type="submit" class="form-control btn <?= esc($settingAplikasi['btn_login'] ?? '', 'attr') ?>" name="submit" id="loginSubmitBtn">Login</button>
			<?= csrf_formfield() ?>
		</div>
</div>
<div class="card-footer">
	<p>Lupa Password? <a href="<?= $config->baseURL ?>recovery">Request reset password</a></p>
	<?php if ($setting_registrasi['enable'] == 'Y') { ?>
		<p>Belum punya akun? <a href="<?= $config->baseURL ?>register">Daftar akun</a></p>
	<?php } ?>
	<p>Tidak menerima link aktivasi? <a href="<?= $config->baseURL ?>register/resendlink">Kirim ulang</a></p>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style type="text/css">
/* Math CAPTCHA Styles */
.math-captcha-container {
	background: #f8f9fa;
	border: 1px solid #dee2e6;
	border-radius: 8px;
	padding: 15px;
	margin-top: 10px;
}
.captcha-label {
	font-size: 13px;
	color: #495057;
	font-weight: 500;
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 12px;
}
.captcha-label i {
	color: #0d6efd;
}
.captcha-wrapper {
	margin-top: 10px;
}
.captcha-question {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	border-radius: 8px;
	padding: 15px;
	text-align: center;
	margin-bottom: 12px;
}
.question-text {
	font-size: 24px;
	font-weight: bold;
	color: #fff;
	text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
	letter-spacing: 2px;
}
.captcha-input-wrapper {
	margin-bottom: 8px;
}
.captcha-input {
	font-size: 18px;
	text-align: center;
	font-weight: 600;
	padding: 12px;
	border: 2px solid #dee2e6;
	border-radius: 6px;
	transition: all 0.3s ease;
}
.captcha-input:focus {
	border-color: #667eea;
	box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
	outline: none;
}
.captcha-hint {
	text-align: center;
	margin-top: 5px;
}
.captcha-hint small {
	font-size: 11px;
}
@media (max-width: 576px) {
	.math-captcha-container {
		padding: 12px;
	}
	.captcha-label {
		font-size: 12px;
	}
	.question-text {
		font-size: 20px;
	}
	.captcha-input {
		font-size: 16px;
		padding: 10px;
	}
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
// Math CAPTCHA - Auto focus pada input
document.addEventListener('DOMContentLoaded', function() {
	const captchaInput = document.getElementById('mathCaptchaAnswer');
	if (captchaInput) {
		// Auto focus setelah halaman dimuat
		setTimeout(function() {
			captchaInput.focus();
		}, 300);
		
		// Validasi input hanya angka
		captchaInput.addEventListener('input', function(e) {
			this.value = this.value.replace(/[^0-9]/g, '');
		});
		
		// Enter key untuk submit
		captchaInput.addEventListener('keypress', function(e) {
			if (e.key === 'Enter') {
				const loginForm = document.querySelector('.form-login');
				if (loginForm && this.value) {
					loginForm.submit();
				}
			}
		});
	}
});
</script>
<?= $this->endSection() ?>