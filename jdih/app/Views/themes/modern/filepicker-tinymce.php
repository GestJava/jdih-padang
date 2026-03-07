<html lang="en" data-bs-theme="<?=$theme_mode?>">
<head>
<title>Jagowebdev File Picker</title>
<meta name="descrition" content="Menu untuk memudahkan memilih file"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?=$config->baseURL?>images/favicon.png?r=<?=time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/fontawesome/css/all.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/bootstrap/css/bootstrap.min.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>themes/modern/builtin/css/bootstrap-custom.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/dropzone/dropzone.min.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/jwdfilepicker/jwdfilepicker.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/jwdfilepicker/jwdfilepicker-loader.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/jwdfilepicker/jwdfilepicker-modal.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>vendors/sweetalert2/sweetalert2.min.css?r=<?=time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL?>themes/modern/builtin/css/dark-theme.css?r=<?=time()?>"/>

<script type="text/javascript">
<?php
	$configFilepicker = new \Config\Filepicker();
?>
var filepicker_server_url = "<?=$configFilepicker->serverURL?>";
var filepicker_icon_url = "<?=$configFilepicker->iconURL?>";
</script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/jquery/jquery.min.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/bootstrap/js/bootstrap.min.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/bootbox/bootbox.min.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/jwdfilepicker/jwdfilepicker.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/dropzone/dropzone.min.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>themes/modern/js/jwdfilepicker-defaults.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>themes/modern/js/filepicker-tinymce.js?r=<?=time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL?>vendors/sweetalert2/sweetalert2.min.js?r=<?=time()?>"></script>
</head>
<body class="filepicker-iframe">
</body>
</html>