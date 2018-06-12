<?php 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Headers: *');
header('Access-Control-Allow-Headers: *');

/*
if (!\OCP\User::isLoggedIn() || !\OCP\App::isEnabled('demoapp')) {
	\OC_Response::setStatus(403);
}
*/

//
//$base_url = "http://drive.bnpmail.com/nc";
define("BASE_URL","http://drive.bnpmail.com");

// Load upload classes
include(__DIR__ . '/Flow/Constant.php');
require_once(__DIR__ . '/Flow/Autoloader.php');
Flow\Autoloader::register();

//read request
if(isset($_POST) && $_POST['type']!='')
{
	$type = $_POST['type'];
	
	switch ($type) {
		case "upload_post":
			$data = nc_upload($_POST);
			break;
		case "upload-old":
			$data = nc_upload_post($_POST);
			break;
		case "upload":
			$data = nc_upload_new($_POST,$_FILES);
			break;
		case "singleupload":
			$data = nc_singleupload($_POST);
			break;
		case "shareurl":
			$data = nc_shareurl($_POST);
			break;
		case "getlist":
			$data = nc_getlist($_POST);
			break;
		case "getdir":
			$data = nc_getdir($_POST);
			break;
		case "getfile":
			$data = nc_getfile($_POST);
			break;
		case "newuser":
			$data = nc_newuser($_POST);
			break;
		case "searchuser":
			$data = nc_searchuser($_POST);
			break;
		case "checkuser":
			$data = nc_checkuser($_POST);
			break;
		case "edituser":
			$data = nc_edituser($_POST);
			break;
		case "userinfo":
			$data = nc_userinfo($_POST);
			break;
		case "sync_single":
			$data = nc_sync_single($_POST);
			break;
		case "sync":
			$data = nc_sync_recursive($_POST);
			break;
		case "adddir":
			$data = nc_adddir($_POST);
			break;
	}
	
	if (isset($data) && !empty($data)) {
		echo json_encode($data);
		exit();
	} 
	else 
	{
		$data = array('status' => 'failed','error' => 1,'message' => NODATA);
		echo json_encode($data);
		exit();
	}
	exit;
}
else{
	$data = array('status' => 'failed','error' => 2,'message' => NODATA);
	echo json_encode($data);
	exit();
}
/*
For Upload
*/
function nc_upload_new($postData,$fileData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	$file_name = $fileData['file']['name'];
	$temp_files = $fileData['file']['tmp_name'];
	$file_size = $fileData['file']['size'];
	$total_size = $postData['total_size'];
	
	$isShareLink = false;
	if($total_size > 25)
		$isShareLink = true;
	
	$root_dir	= $postData['root_path'];
	$data_share = array();
	$userhome = OC_User::getHome(OC_User::getUser());
	
	$temp = $userhome.'/.tmp/';
	
	if($root_dir !='')
		$result = '/'.$root_dir.'/';
	else
		$result ='/';
		
	// Initialize uploader
	$config = new \Flow\Config();
	$config->setTempDir($temp);
	
	$path  = $file_name;
	if (\OC\Files\Filesystem::isValidPath($path)) 
	{
		if(!file_exists($temp)) {
			mkdir($temp);
		}
		
		$share_path = $result . $path;
		$dir = dirname($result . $path);
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);
		
		if(move_uploaded_file($temp_files, $userhome . "/files/" . $result . $path))
		{
			if($isShareLink == true)
				$data_share[] = nc_getSharePath($result.$path,$header);
			
			\OC\Files\Filesystem::touch($result . $path);
		} 
		else {
			if($isShareLink == true)
				$data_share[] = array('path'=>$result.$path,'share_path'=>'');
		}
		
		\Flow\Uploader::pruneChunks($temp);
		
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
		
	}
	return $response;
	exit;
}

function nc_upload_new1($postData,$fileData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	//print_r($fileData);exit;
	if(!empty($fileData) && is_array($fileData['image']['name']) )
	{
		$file_names = $fileData['image']['name'];
		$temp_files = $fileData['image']['tmp_name'];
		$file_size = $fileData['image']['size'];
		
		$paths = '';
		if(isset($postData['paths']) && $postData['paths']!='')
			$paths = explode("###", rtrim($postData['paths'], "###"));
		
		$total_size = array_sum($file_size);
		$isShareLink = false;
		if($total_size > 26214400)
			$isShareLink = true;
		
		$root_dir	= $postData['root_path'];
		$data_share = array();
		$userhome = OC_User::getHome(OC_User::getUser());
		foreach ($temp_files as $key=>$temp_file)
		{
			if(isset($postData['paths']) && $postData['paths']!='')
				$file_name = $paths[$key];
			else
				$file_name = $file_names[$key];
			
			$temp = $userhome.'/'.$root_dir.'_tmp/';
			if($root_dir !='' && $root_dir !='/')
				$result = '/'.$root_dir.'/';
			else
				$result ='/';
			$file_data = $temp_file;
			
			// Initialize uploader
			$config = new \Flow\Config();
			$config->setTempDir($temp);
			
			$path  = $file_name;
			if (\OC\Files\Filesystem::isValidPath($path)) 
			{
				// Create temporary upload folder
				if(!file_exists($temp)) {
					mkdir($temp);
				}
				
				// Create upload folder
				$share_path = $result . $path;
				$dir = dirname($result . $path);
				if(!\OC\Files\Filesystem::file_exists($dir)) {
					\OC\Files\Filesystem::mkdir($dir);
				}
				
				// Store file
				$header_auth = getallheaders()['Authorization'];
				$header = array(
					"Accept:application/json",
					"Authorization: $header_auth",
					"OCS-APIRequest: true"
				);
				
				
				// Store file
				//if(file_put_contents($userhome . "/files/" . $result . $path, $file_data))
				if(move_uploaded_file($file_data, $userhome . "/files/" . $result . $path))
				{
					if($isShareLink == true)
						$data_share[] = nc_getSharePath($result.$path,$header);
					
					\OC\Files\Filesystem::touch($result . $path);
				} 
				else {
					if($isShareLink == true)
						$data_share[] = array('path'=>$result.$path,'share_path'=>'');
				}
				
				\Flow\Uploader::pruneChunks($temp);
			}
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	else if(!empty($fileData) && !is_array($fileData['image']['name']) )
	{
		//print_r($fileData);exit;
		$file_names = $fileData['image']['name'];
		$temp_files = $fileData['image']['tmp_name'];
		$file_size = $fileData['image']['size'];
		
		$paths = '';
		if(isset($postData['paths']) && $postData['paths']!='')
			$paths = explode("###", rtrim($postData['paths'], "###"));
		
		$total_size = $file_size;
		
		$isShareLink = false;
		if($total_size > 26214400)
			$isShareLink = true;
		
		$root_dir	= $postData['root_path'];
		$data_share = array();
		$userhome = OC_User::getHome(OC_User::getUser());
		
		if(isset($postData['paths']) && $postData['paths']!='')
			$file_name = $paths[$key];
		else
			$file_name = $file_names;
		
		$temp = $userhome.'/'.$root_dir.'_tmp/';
		if($root_dir !='' && $root_dir !='/')
				$result = '/'.$root_dir.'/';
			else
				$result ='/';
		$file_data = $temp_files;
		
		// Initialize uploader
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			// Create temporary upload folder
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir);
			}
			
			// Store file
			$header_auth = getallheaders()['Authorization'];
			$header = array(
				"Accept:application/json",
				"Authorization: $header_auth",
				"OCS-APIRequest: true"
			);
			
			// Store file
			//if(file_put_contents($userhome . "/files/" . $result . $path, $file_data))
			if(move_uploaded_file($file_data, $userhome . "/files/" . $result . $path))
			{
				if($isShareLink == true)
					$data_share[] = nc_getSharePath($result.$path,$header);
				
				\OC\Files\Filesystem::touch($result . $path);
			} 
			else {
				if($isShareLink == true)
					$data_share[] = array('path'=>$result.$path,'share_path'=>'');
			}
			
			\Flow\Uploader::pruneChunks($temp);
		}
		
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	return $response;
	exit;
}

function nc_singleupload($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	if(!empty($postData))
	{
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		
		$file_name = $postData['name'];
		$temp = $userhome.'/'.$postData['dir'].'_tmp/';
		$result = '/'.$postData['dir'].'/';
		$tmp_name = $postData['file'];
		
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			// Create temporary upload folder
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir);
			}
			
			$header_auth = getallheaders()['Authorization'];
			$header = array(
				"Accept:application/json",
				"Authorization: $header_auth",
				"OCS-APIRequest: true"
			);
			
			// Store file
			if(file_put_contents($userhome . "/files/" . $result . $path, $tmp_name))
			{
				$data_share = nc_getSharePath($result.$path,$header);
				
				\OC\Files\Filesystem::touch($result . $path);
			} 
			else {
				$data_share = array('path'=>$result.$path,'share_path'=>'');
			}
			
			\Flow\Uploader::pruneChunks($temp);
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	echo json_encode($response);
	exit;
}

function nc_upload_post1($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	$files = $postData['file_data'];
	$file_size = $postData['size'];
	if(!empty($files))
	{
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		foreach($files as $key=>$file)
		{
			$file_name = $file['name'];
			$temp = $userhome.'/'.$file['dir'].'_tmp/';
			$result = '/'.$file['dir'].'/';
			$tmp_name = $file['file'];
			
			// Initialize uploader
			$config = new \Flow\Config();
			$config->setTempDir($temp);
			
			$path  = $file_name;
			if (\OC\Files\Filesystem::isValidPath($path)) 
			{
				// Create temporary upload folder
				if(!file_exists($temp)) {
					mkdir($temp);
				}
				
				// Create upload folder
				$share_path = $result . $path;
				$dir = dirname($result . $path);
				if(!\OC\Files\Filesystem::file_exists($dir)) {
					\OC\Files\Filesystem::mkdir($dir);
				}
				
				$header_auth = getallheaders()['Authorization'];
				$header = array(
					"Accept:application/json",
					"Authorization: $header_auth",
					"OCS-APIRequest: true"
				);
				
				// Store file
				if(file_put_contents($userhome . "/files/" . $result . $path, $tmp_name))
				{
					$data_share[] = nc_getSharePath($result.$path,$header);
					
					\OC\Files\Filesystem::touch($result . $path);
				} 
				else {
					$data_share[] = array('path'=>$result.$path,'share_path'=>'');
				}
				
				\Flow\Uploader::pruneChunks($temp);
			}
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	echo json_encode($response);
	exit;
}

function nc_upload($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	$files = json_decode($postData['file_data'],true);
	$file_size = $postData['size'];
	if(!empty($files))
	{
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		foreach($files as $file)
		{
			$file_name = $file['name'];
			$temp = $userhome.'/'.$file['dir'].'_tmp/';
			$result = '/'.$file['dir'].'/';
			$file_data = $file['image'];
			$data = base64_decode($file_data);
			//$file_size = file['size'];
			
			// Initialize uploader
			$config = new \Flow\Config();
			$config->setTempDir($temp);
			
			$path  = $file_name;
			if (\OC\Files\Filesystem::isValidPath($path)) 
			{
				// Create temporary upload folder
				if(!file_exists($temp)) {
					mkdir($temp);
				}
				
				// Create upload folder
				$share_path = $result . $path;
				$dir = dirname($result . $path);
				if(!\OC\Files\Filesystem::file_exists($dir)) {
					\OC\Files\Filesystem::mkdir($dir);
				}
				
				// Store file
				if(file_put_contents($userhome . "/files/" . $result . $path, $data))
				{
					$data_share[] = nc_getSharePath($result.$path);
					
					\OC\Files\Filesystem::touch($result . $path);
				} 
				else {
					$data_share[] = array('path'=>$result.$path,'share_path'=>'');
				}
				
				\Flow\Uploader::pruneChunks($temp);
			}
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	echo json_encode($response);
	exit;
}
/*
For Share Url
*/
function nc_shareurl($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['path']) &&$postData['path']!='')
	{
		$path = $postData['path'];
		$response = array('status' => 'failed','error' => 1,'message' => 'ok');
		$path = $postData['path'];
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);
		
		$getoutput = nc_getSharePath($path,$header);
		$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$getoutput);
	}
	return $response;
	exit;
}
/*
Create Share Url for File/Folder
*/
function nc_getSharePath($path='',$header=null)
{
	if($path=='')
		return '';
	
	$surl  = BASE_URL.'/nc/ocs/v1.php/apps/files_sharing/api/v1/shares';
	
	$data = array(
		"path" => $path,
		"shareType" => 3
	);
	
	$data = http_build_query($data);
	
	$ch   = curl_init();
	curl_setopt($ch, CURLOPT_URL, $surl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close($ch);
	$result = json_decode($server_output,true);
	$share_url = $result['ocs']['data']['url'];
	return array('path'=>$path,'share_path'=>$share_url);
	
}

function nc_getSharePath1($path='',$header=null)
{
	if($path=='')
		return '';
	
	$surl  = BASE_URL.'/nc/ocs/v1.php/apps/files_sharing/api/v1/shares';
	$data = array(
		"path" => $path,
		"shareType" => 3
	);
	
	$header = array(
		"Accept:application/json",
		"Authorization: Basic " . base64_encode("admin:admin#123"),
		"OCS-APIRequest: true"
	);
	
	//foreach ($postData as $key=>$value)
	{
		//$post_url .= $key.'='.$value.'&'; 
	}
	//$data = rtrim($post_url, '&'); 

	$data = http_build_query($data);
	//parse_str($inputStr, $data);
	$ch   = curl_init();
	curl_setopt($ch, CURLOPT_URL, $surl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec($ch);
	curl_close($ch);
	
	$result = json_decode($server_output,true);
	$share_url = $result['ocs']['data']['url'];
	
	return array('path'=>$path,'share_path'=>$share_url);
	
}

function nc_getlist()
{
	echo "List Data";
	exit;
}
/*
For Folders
*/
function nc_getdir($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['path']) &&$postData['path']!='')
	{
		$response = array('status' => 'failed','error' => 1,'message' => 'ok');
		$path = $postData['path'];
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:text/plain",
			"Authorization: $header_auth"
		);
		
		$url = "/nc/remote.php/webdav".$path;
		
		$getoutput = curl_post(1,$header,$url);
		$response = str_replace("d:","",$getoutput);
		$xml = simplexml_load_string($response); 	//json_decode(simplexml_load_string($response),true);
		
		$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$xml);
	}
	return $response;
	exit;
}
/*
For File
*/
function nc_getfile($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['path']) &&$postData['path']!='')
	{
		$response = array('status' => 'failed','error' => 1,'message' => 'ok');
		$path = $postData['path'];
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:text/plain",
			"Authorization: $header_auth"
		);
		
		$url = $path;
		
		$getoutput = curl_post(2,$header,$url);
		$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>base64_encode($getoutput));
		
	}
	return $response;
	exit;
}
/*
Curl Request
*/
function curl_post($type=0,$header=null,$url=null,$data=null)
{
	$url = BASE_URL.$url;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	
	if($type ==1)
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
	
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	if($data!=null)
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}
/*
For Newuser
*/
function nc_newuser($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['userid']) && isset($postData['password']) && $postData['userid']!='' && $postData['password']!='')
	{
		$data = array(
			"userid" => $postData['userid'],
			"password" => $postData['password']
		);
		
		$url  = '/nc/ocs/v1.php/cloud/users';
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);
		
		$getoutput = ocs_curl_post($header,$url,$data);
		$result = json_decode($getoutput,true);
		
		if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
		{
			$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
		}
		else
		{
			$response = array('status' => 'failed','error' => 1,'message' => $result['ocs']['meta']['message']);
		}
	}
	return $response;
	exit;
}
/*
For Search User
*/
function nc_searchuser($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['userid']) && $postData['userid']!='')
	{
		$search = $postData['userid'];
		$url  = '/nc/ocs/v1.php/cloud/users?search='.$search;
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);

		$getoutput = ocs_curl_post($header,$url);
		$result = json_decode($getoutput,true);
		$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
	}
	return $response;
	exit;
}
/*
For User Check
*/
function nc_checkuser($postData)
{
	
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['userid']) && $postData['userid']!='' && isset($postData['password']) && $postData['password']!='')
	{
		$userid = $postData['userid'];
		$key = 'password';
		$value = $postData['password'];
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:text/plain",
			"Authorization:$header_auth",
			"Content-type: application/x-www-form-urlencoded",
			"OCS-APIRequest: true"
		);
		
		$getoutput = ocs_request_nextcloud($header,['url' => 'ocs/v1.php/cloud/users/'.$userid,'put' => 'key=' . $key . '&value=' . $value]);
		if($getoutput)
		{
			$response = array('status' => 'success','error' => 0,'message' => 'Data updated successfully.', 'data'=>$result);
		}
		else
		{
			$response = array('status' => 'failed','error' => 1,'message' => 'Unable to update.');
		}
		
		//$response = array('status' => 'success','error' => 0,'message' => 'Data updated successfully.', 'data'=>$result);
	}
	return $response;
	exit;
	
}


function nc_checkuser1($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['userid']) && $postData['userid']!='')
	{
		$userid = $postData['userid'];
		$url  = '/nc/ocs/v1.php/cloud/users/'.$userid;
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);

		$getoutput = ocs_curl_post($header,$url);
		$result = json_decode($getoutput,true);
		if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
		{
			$key = 'password';
			$value = $postData['password'];
			
			$header = array(
				"Accept:text/plain",
				"Authorization:$header_auth",
				"Content-type: application/x-www-form-urlencoded",
				"OCS-APIRequest: true"
			);
			
			$getoutput = ocs_request_nextcloud($header,['url' => 'ocs/v1.php/cloud/users/'.$userid,'put' => 'key=' . $key . '&value=' . $value]);
			$response = array('status' => 'success','error' => 0,'message' => 'Data updated successfully.', 'data'=>$result);
			//$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
		}
		else
		{
			$data = array(
				"userid" => $postData['userid'],
				"password" => $postData['password']
			);
			
			$url  = '/nc/ocs/v1.php/cloud/users';
			$header = array(
				"Accept:application/json",
				"Authorization: $header_auth",
				"OCS-APIRequest: true"
			);
			
			$getoutput = ocs_curl_post($header,$url,$data);
			$result = json_decode($getoutput,true);
			
			if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
			{
				$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
			}
			else
			{
				$response = array('status' => 'failed','error' => 1,'message' => $result['ocs']['meta']['message']);
			}
			
			//$response = nc_newuser($postData)
			//$response = array('status' => 'failed','error' => 1,'message' => $result['ocs']['meta']['message']);
		}
	}
	return $response;
	exit;
}

function nc_checkuser_old($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['userid']) && $postData['userid']!='')
	{
		$userid = $postData['userid'];
		$url  = '/nc/ocs/v1.php/cloud/users/'.$userid;
		
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);

		$getoutput = ocs_curl_post($header,$url);
		$result = json_decode($getoutput,true);
		if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
		{
			$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
		}
		else
		{
			$response = array('status' => 'failed','error' => 1,'message' => $result['ocs']['meta']['message']);
		}
	}
	return $response;
	exit;
}

/*
For Newuser
*/
function nc_edituser($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData) && isset($postData['key']) && isset($postData['value']) && isset($postData['userid']) && $postData['key']!='' && $postData['value']!='' && $postData['userid']!='')
	{
		
		$userid = $postData['userid'];
		$key = $postData['key'];
		$value = $postData['value'];
		
		/*
		$data = array(
			"key" => $postData['key'],
			"value" => $postData['value']
		);

		$url  = "/nc/ocs/v1.php/cloud/users/$userid";
		
		$header_auth = getallheaders()['Authorization'];
		
		$header1 = array(
			"Accept:application/json",
			"Authorization: $header_auth",
			"OCS-APIRequest: true"
		);
			//$getoutput = ocs_curl_post($header,$url,$data);
		*/
		$header_auth = getallheaders()['Authorization'];
		$header = array(
			"Accept:text/plain",
			"Authorization:$header_auth",
			"Content-type: application/x-www-form-urlencoded",
			"OCS-APIRequest: true"
		);
		
		$getoutput = ocs_request_nextcloud($header,['url' => 'ocs/v1.php/cloud/users/'.$userid,'put' => 'key=' . $key . '&value=' . $value]);
		if($getoutput)
		{
			$response = array('status' => 'success','error' => 0,'message' => 'Data updated successfully.', 'data'=>array());
		}
		else
		{
			$response = array('status' => 'failed','error' => 1,'message' => 'Unable to update.');
		}
		
		
		/*
		$result = json_decode($getoutput,true);
		
		if(isset($result['ocs']['meta']['status']) && $result['ocs']['meta']['status']!='failure')
		{
			$response = array('status' => 'success','error' => 0,'message' => 'Success', 'data'=>$result);
		}
		else
		{
			$response = array('status' => 'failed','error' => 1,'message' => $result['ocs']['meta']['message']);
		}
		*/
	}
	return $response;
	exit;
}
/*
OCS Curl Request
*/
function ocs_request_nextcloud($header,$data) {

	$curl = curl_init(BASE_URL.'/nc/'.$data['url']);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	
	if (key_exists('put', $data)) 
	{
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data['put']);
	}
	return json_decode(curl_exec($curl));
}

function ocs_curl_post($header=null,$url=null,$data=null)
{
	$url = BASE_URL.$url;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	if($data!=null)
	{
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}


function nc_sync_single($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	if(!empty($postData))
	{
		//return $postData;
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		
		$file_name = rand().$postData['file_name'];
		$temp = $userhome.'/sync_tmp/';
		$result = '/'.$postData['destination'].'/';
		$file = $postData['source_url'].'/'.$postData['source'].'/'.$file_name;
		
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir);
			}
			$file = 'https://images.pexels.com/photos/67636/rose-blue-flower-rose-blooms-67636.jpeg';
			//if(file_put_contents($userhome . "/files/" . $result . $path, $tmp_name))
			if(copy($file, $userhome . "/files/" . $result . $path))
			{
				\OC\Files\Filesystem::touch($result . $path);
			} 
			else {
				//
			}
			\Flow\Uploader::pruneChunks($temp);
			
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$file);
	}
	
	return $response;
	exit;
}

function nc_sync_old($postData)
{
	exit;
	if(!empty($postData))
	{
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		
		$file_name = $postData['name'];
		$temp = $userhome.'/'.$postData['dir'].'_tmp/';
		$result = '/'.$postData['dir'].'/';
		$tmp_name = $postData['file'];
		
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			// Create temporary upload folder
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir);
			}
			
			$header_auth = getallheaders()['Authorization'];
			$header = array(
				"Accept:application/json",
				"Authorization: $header_auth",
				"OCS-APIRequest: true"
			);
			
			// Store file
			if(file_put_contents($userhome . "/files/" . $result . $path, $tmp_name))
			{
				$data_share = nc_getSharePath($result.$path,$header);
				
				\OC\Files\Filesystem::touch($result . $path);
			} 
			else {
				$data_share = array('path'=>$result.$path,'share_path'=>'');
			}
			
			\Flow\Uploader::pruneChunks($temp);
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
	}
	echo json_encode($response);
	exit;
}

function nc_sync_recursive($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => UPLOAD_FAILED);
	if(!empty($postData))
	{
		$source_url = "/var/www/html/nc/upload/server1";
		$destination_url = "http://drive.bnpmail.com/nc";

		//return $postData;
		$userhome = OC_User::getHome(OC_User::getUser());
		$data_share = array();
		
		$files  = $postData['files'];
		if(!is_array($files))
		{
			$files  = json_decode($postData['files'],true);
		}
		
		foreach($files as $file )
		{
			$date_str = $file['date'];
			$date = DateTime::createFromFormat("d/m/Y", $date_str);
			$source_dir = $destination_dir = $date->format("Y");
			
			//
			$file_name = $file['file_name'];
			$temp = $userhome.'/sync_tmp/';
			$result = '/'.$destination_dir.'/';
			$file = $source_url.'/'.$source_dir.'/'.$file_name;
			
			$config = new \Flow\Config();
			$config->setTempDir($temp);
			
			$path  = $file_name;
			if (\OC\Files\Filesystem::isValidPath($path)) 
			{
				if(!file_exists($temp)) {
					mkdir($temp);
				}
				// Create upload folder
				$share_path = $result . $path;
				$dir = dirname($result . $path);
				if(!\OC\Files\Filesystem::file_exists($dir)) {
					\OC\Files\Filesystem::mkdir($dir);
				}
				
				//$file = 'https://images.pexels.com/photos/67636/rose-blue-flower-rose-blooms-67636.jpeg';
				
				//if(file_put_contents($userhome . "/files/" . $result . $path, $tmp_name))
				if(copy($file, $userhome . "/files/" . $result . $path))
				{
					\OC\Files\Filesystem::touch($result . $path);
				} 
				else {
					//
				}
				\Flow\Uploader::pruneChunks($temp);
				
			}
			//$data_share[] = $file;
		}
		$response = array('status' => 'success','error' => 0,'message' => UPLOAD_SUCCESS, 'data'=>$data_share);
		
	}
	return $response;
	exit;
}

function nc_adddir_test($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData))
	{
		ListFolder('');
		exit;
	
		$userhome = OC_User::getHome(OC_User::getUser());
		//print_r($postData);
		
		$dir = $postData['path'];
		$temp = $userhome.'/.test/';
		$result = '/test/';
		$file_name = 'dummy.txt';
		
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			// Create temporary upload folder
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir,0777,true);
			}
			
			\OC\Files\Filesystem::touch($result . $path);
			\Flow\Uploader::pruneChunks($temp);
		}
		
		exit;
	}
	
	return $response;
}


function nc_adddir($postData)
{
	$response = array('status' => 'failed','error' => 1,'message' => NODATA);
	if(!empty($postData))
	{
		$response = ListFolder('');
	}
	return $response;
}

function ListFolder($path)
{
	$userhome = OC_User::getHome(OC_User::getUser())."/files/";
	
	if($path =='')
		$path = $userhome;
	
	$dir_handle = @opendir($path) or die("Unable to open $path");
	$dirname = end(explode("/", $path));
	$data_dir = ltrim($path,'.//');
	
	$createdir = explode("files/", $path);
	$newpath = $createdir[1];

	/*
	Create Path
	*/
	if($newpath!='')
	{
		$dir = $newpath;
		$temp = $userhome.'/.test/';
		$result = $newpath.'/';
		$file_name = 'dummy.txt';
		
		$config = new \Flow\Config();
		$config->setTempDir($temp);
		
		$path  = $file_name;
		if (\OC\Files\Filesystem::isValidPath($path)) 
		{
			// Create temporary upload folder
			if(!file_exists($temp)) {
				mkdir($temp);
			}
			
			$path  = '';
			// Create upload folder
			$share_path = $result . $path;
			$dir = dirname($result . $path);
			if(!\OC\Files\Filesystem::file_exists($dir)) {
				\OC\Files\Filesystem::mkdir($dir,0777,true);
			}
			
			\OC\Files\Filesystem::touch($result . $path);
			\Flow\Uploader::pruneChunks($temp);
		}
	}
	
	while (false !== ($file = readdir($dir_handle))) 
	{
		if($file!="." && $file!="..")
		{
			if (is_dir($path."/".$file))
			{
				ListFolder($path."/".$file);
			}
		}
	}
	//closing the directory
	closedir($dir_handle);
	return $response = array('status' => 'success','error' => 0,'message' => 'Request completed', 'data'=>array());
}
?>