<?php
// Restrict access // ToDo: Enabled for current user?
if (!\OCP\User::isLoggedIn() || !\OCP\App::isEnabled('demoapp')) {
	\OC_Response::setStatus(403);
}

// Load upload classes
require_once(__DIR__ . '/Flow/Autoloader.php');
Flow\Autoloader::register();

// Directory definitions
$userhome = OC_User::getHome(OC_User::getUser());
$temp = $userhome.'/.popupupload_tmp/';
//$result = '/popupupload/';

$result = (isset($_REQUEST['dir']))?$_REQUEST['dir'].'/':'/';

// Initialize uploader
$config = new \Flow\Config();
$config->setTempDir($temp);
$request = new \Flow\Request();

// Filter paths
$path = preg_replace('/(\.\.\/|~|\/\/)/i', '', $request->getRelativePath());
$path = preg_replace('/[^a-z0-9äöüßáàâãéèêíìîóòõôúùûºªç&$%*#@ \(\)\.\-_\/]/i', '', $path);
$path = trim($path, '/');

// Skip existing files // ToDo: Check if file size changed?
if (\OC\Files\Filesystem::file_exists($result . $path)) {
	//\OC_Response::setStatus(200);
	//die();
}

// Process upload
if (\OC\Files\Filesystem::isValidPath($path)) {

	// Create temporary upload folder
	if(!file_exists($temp)) {
		mkdir($temp);
	}

	// Create destination directory
	$dir = dirname($result . $path);
	if(!\OC\Files\Filesystem::file_exists($dir)) {
		\OC\Files\Filesystem::mkdir($dir);
	}

	// Store file
	if (\Flow\Basic::save($userhome . "/files/" . $result . $path, $config, $request)) {
		\OC\Files\Filesystem::touch($result . $path);

	} else {
		// This is not a final chunk or request is invalid, continue to upload.
	}

	// Remove old chunks
	\Flow\Uploader::pruneChunks($temp);
}
?>
