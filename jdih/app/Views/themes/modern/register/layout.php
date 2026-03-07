<!DOCTYPE HTML>
<html lang="en">
<title><?= $site_title ?></title>
<meta name="descrition" content="<?= $site_desc ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?= base_url('images/favicon.png?v=1.0.1') ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url('vendors/bootstrap/css/bootstrap.min.css?v=5.3.0') ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url('vendors/fontawesome/css/all.css?v=5.15.4') ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url('themes/modern/css/register.css?v=1.0.2') ?>" />

<?php
if (@$styles) {
	foreach ($styles as $file) {
		echo '<link rel="stylesheet" type="text/css" href="' . $file . '?v=1.0.1"/>';
	}
}

?>

<script type="text/javascript" src="<?= base_url('vendors/jquery/jquery.min.js?v=3.6.0') ?>"></script>
<script type="text/javascript" src="<?= base_url('vendors/bootstrap/js/bootstrap.min.js?v=5.3.0') ?>"></script>

<?php

if (@$scripts) {
	foreach ($scripts as $file) {
		echo '<script type="text/javascript" src="' . $file . '?v=1.0.1"/></script>';
	}
}

?>

</html>

<body>
	<div class="background"></div>
	<div class="backdrop"></div>
	<div class="card-container" <?= @$style ?>>
		<?php
		$this->renderSection('content')
		?>
		<div class="copyright">
			<?php $footer = $settingAplikasi['footer_login'] ? str_replace('{{YEAR}}', date('Y'), html_entity_decode($settingAplikasi['footer_login'])) : '';
			echo $footer;
			?>
		</div>
	</div><!-- login container -->
</body>

</html>