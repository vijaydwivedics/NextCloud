<?php
$this->create('demoapp_index', '/')
	->actionInclude('demoapp/index.php');

$this->create('demoapp_ajax_upload_api', 'ajax/upload_api.php')
	->actionInclude('demoapp/ajax/upload_api.php');
	
$this->create('demoapp_ajax_upload', 'ajax/upload.php')
	->actionInclude('demoapp/ajax/upload.php');

return [
	'resources' => [
		'ExternalShares' => ['url' => '/api/externalShares'],
	],
	'routes' => [
		[
			'name' => 'externalShares#testRemote',
			'url' => '/testremote',
			'verb' => 'GET'
		],
		[
			'name' => 'PublicPreview#getPreview',
			'url' => '/publicpreview',
			'verb' => 'GET',
		],

		[
			'name' => 'PublicPreview#getPreview',
			'url' => '/ajax/publicpreview.php',
			'verb' => 'GET',
		],

		[
			'name' => 'ShareInfo#info',
			'url' => '/shareinfo',
			'verb' => 'POST',
		],
	],
	'ocs' => [
		/*
		 * OCS Share API
		 */
		[
			'name' => 'ShareAPI#getShares',
			'url'  => '/api/v1/shares',
			'verb' => 'GET',
		],
		[
			'name' => 'ShareAPI#createShare',
			'url'  => '/api/v1/shares',
			'verb' => 'POST',
		],
		[
			'name' => 'ShareAPI#getShare',
			'url'  => '/api/v1/shares/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'ShareAPI#updateShare',
			'url'  => '/api/v1/shares/{id}',
			'verb' => 'PUT',
		],
		[
			'name' => 'ShareAPI#deleteShare',
			'url'  => '/api/v1/shares/{id}',
			'verb' => 'DELETE',
		],
		/*
		 * OCS Sharee API
		 */
		[
			'name' => 'ShareesAPI#search',
			'url' => '/api/v1/sharees',
			'verb' => 'GET',
		],
		/*
		 * Remote Shares
		 */
		[
			'name' => 'Remote#getShares',
			'url' => '/api/v1/remote_shares',
			'verb' => 'GET',
		],
		[
			'name' => 'Remote#getOpenShares',
			'url' => '/api/v1/remote_shares/pending',
			'verb' => 'GET',
		],
		[
			'name' => 'Remote#acceptShare',
			'url' => '/api/v1/remote_shares/pending/{id}',
			'verb' => 'POST',
		],
		[
			'name' => 'Remote#declineShare',
			'url' => '/api/v1/remote_shares/pending/{id}',
			'verb' => 'DELETE',
		],
		[
			'name' => 'Remote#getShare',
			'url' => '/api/v1/remote_shares/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'Remote#unshare',
			'url' => '/api/v1/remote_shares/{id}',
			'verb' => 'DELETE',
		],
	],
];
