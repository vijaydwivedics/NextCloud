<?php
/*
\OC::$server->getNavigationManager()->add(array(
	'id'    => 'demoapp',
	'order' => 74,
	'href' 	=> \OCP\Util::linkToRoute('demoapp_index'),
	'icon'  => \OCP\Util::imagePath('demoapp', 'flowupload.svg'),
	'name' 	=> \OC::$server->getL10N('demoapp')->t('DemoApp')
));
*/

\OC::$server->getNavigationManager()->add(array(
	'id'    => 'reloadApp',
	'order' => 75,
	'icon'  => \OCP\Util::imagePath('demoapp', 'mount.svg'),
	'name'  => \OC::$server->getL10N('demoapp')->t('Reload File')
));

\OC::$server->getNavigationManager()->add(array(
	'id'    => 'demoapp',
	'order' => 74,
	'icon'  => \OCP\Util::imagePath('demoapp', 'popupload.svg'),
	'name'  => \OC::$server->getL10N('demoapp')->t('Upload Popup')
));
//====
\OCP\Util::addScript('demoapp', 'popup-script');
\OCP\Util::addStyle('files', 'upload');