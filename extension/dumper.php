<?php
$allowFiles = array("gif","png","jpg","jpeg");

if(!$_POST['slug'])
die('You need a slug to dump your files');

if(is_numeric($_POST['series_id']))
	$series = $db->series_detail($_POST['series_id']);
else
	die('Something went wrong!');
	
$table = $wpdb->prefix."comic_chapter";
if($wpdb->get_var("SELECT number FROM ".$table." WHERE number = '".$_CLEAN['number']."' AND series_id = '".$_CLEAN['series_id']."'") == $_CLEAN['number'])  
	if (($_POST['action'] != 'update') || ($_POST['action'] == "update" && $_OLD['number'] != $_CLEAN['number']))
		$chapter['fail']['number']['duplicate'] = true;

//Fantasier fix here
if($wpdb->get_var("SELECT slug FROM `".$table."` WHERE slug = '".$_CLEAN['slug']."' AND series_id = '".$_CLEAN['series_id']."'") == $_CLEAN['slug']) 
	if (($_POST['action'] != 'update') || ($_POST['action'] == "update" && $oldSeries['slug'] != $_CLEAN['slug']))
		$chapter['fail']['slug'] = true;
		
if (!is_numeric($_POST['number'])) 
	$chapter['fail']['number']['character'] = true;
		
//Preset Vars
$pubdate = date("Y-m-d H:i:s O");
$uploader = $current_user->ID;
$title = $_POST['title'];
$chapterSlug = urlencode($_POST['slug']);
$seriesFolder .= '/'.strtolower($series["slug"]).'/';
$chapterFolder =  $seriesFolder.$chapterSlug.'/';
$extractFolder = UPLOAD_FOLDER.$chapterFolder;
if(!is_float($_POST['number']) && is_numeric($_POST['number']))
	$chapterNumber = intval($_POST['number']);
	 
//Check that we have a file
if((!empty($_FILES["zip"])) && ($_FILES['zip']['error'] == 0)) {
	if(!class_exists('ZipArchive')) die("The ZipArchive Library (zlib) is not enable. Can not extract without the library.");
	$zipclass = new ZipArchive();	
    $ext = substr($_FILES['zip']['name'], strrpos($_FILES['zip']['name'], '.') + 1);
    
    $var = intval(preg_replace("/[^0-9]/", '', ini_get('upload_max_filesize')));
    $maxFileSize = 1048576 * $var; 
  	$newname = UPLOAD_FOLDER.$seriesFolder.'uploads/'.$chapterSlug.'-'.$_FILES['zip']['name'];	
		
    if($ext == "zip") {        
        $zipclass->open($newname);
        for ($i=0; $i<$zipclass->numFiles;$i++) {
            $zipArray = $zipclass->statIndex($i);
            $zipExt = substr($zipArray['name'], strrpos($zipArray['name'], '.') + 1);
            if(!in_array(strtolower($zipExt),$allowFiles))
                $zip['error']['invalid_files'] = true; 
        }
		$zipFile = true;
    } else if(($ext == "rar") && ($_FILES['zip']['error'] == 0)) {
        if(function_exists('rar_open')) {
			$rar_file = rar_open($newname);
			$files = rar_list($rar_file);

			foreach ($files as $file) {
				$zipExt = substr($file->getName(), strrpos($file->getName(), '.') + 1);
				if(!in_array(strtolower($zipExt),$allowFiles))
					$zip['error']['invalid_files'] = true; 
			}
			$rarFile = true;
			rar_close($rar_file);
		} else {
			die('Rar function does not exist on this server. You won\'t be able to Extract Rar Files without the Rar Module.');
		}
	}
    //$_FILES["zip"]["size"] < $maxFileSize // This was to limit what is being uploaded...
	if(!$zip['error']) {

		//Check if the Upload Directory Exist
        if(!is_dir(UPLOAD_FOLDER.$seriesFolder.'uploads/')) 
	        mkdir(UPLOAD_FOLDER.$seriesFolder.'uploads/');
        	
		//Check if the Chapter Directory Exist
        if(!is_dir(UPLOAD_FOLDER.$seriesFolder.$chapterSlug.'/'))
	        mkdir(UPLOAD_FOLDER.$seriesFolder.$chapterSlug.'/');
			
		//Move the Zip File Location to the Temporary Upload Directory
        if(is_dir(UPLOAD_FOLDER.$seriesFolder.$chapterSlug.'/'))
	        move_uploaded_file($_FILES['zip']['tmp_name'],$newname);
        
	} else {    
		$zip['error']['toolarge'] = true; 
        $status['error'] = "The zip/rar file is larger than ". $var ."mb";
    }
	
} else {
    //No File Uploaded
    var_dump($_FILES['zip']);
    $zip['fail']['nofile'] = true;
    $status['error'] = "There were no files to upload. If you uploaded a file, please keep in mind that your max upload filesize from your webhost is: ".ini_get('upload_max_filesize');
}

//Extract
if(file_exists($newname) && !$chapter['fail'] && $zipFile == true) {
	$aZip = new ZipArchive();
	$aZip->open($newname);
	$aZip->extractTo($extractFolder);
	unlink($newname);
	
	$chapterID = $db->chapter_create($title,$chapterNumber,$summary,$_POST['series_id'],$pubdate,$chapterSlug,$scanlator,$scanlator_slug,0,$chapterFolder,true) or die('Something went wrong when creating the Chapter.');
	$fileExtracted = getFileList($extractFolder);
	if(count($fileExtracted) == 1 || is_dir($fileExtracted[0]))
		$fileExtracted = getFileList($fileExtracted[0]);
		
	sort($fileExtracted);
		for ($count = 0 ; $count < count($fileExtracted); $count++) {
	        $fileArray = $fileExtracted[$count];
	        
	        if (!is_dir($fileArray) && $fileArray != "." && $fileArray != "..") {
				$baseFileName = str_replace($extractFolder,'',$fileArray);;
					
				$db->page_create('',$count,$baseFileName,$pubdate,'',$count,$_POST['series_id'],$chapterID,'');
	        } 
		}
	$status['pass'] = "The zipped chapter has been dumped.";
} else if (file_exists($newname) && !$chapter['fail'] && $rarFile == true) {
		$rar_file = rar_open($newname);
		$files = rar_list($rar_file);
		foreach ($files as $file) {
			$file->extract($extractFolder);
		}
	$chapterID = $db->chapter_create($title,$chapterNumber,$summary,$_POST['series_id'],$pubdate,$chapterSlug,$scanlator,$scanlator_slug,0,$chapterFolder,$folder,true);
	$fileExtracted = getFileList($extractFolder);
	if(count($fileExtracted) == 1 || is_dir($fileExtracted[0]))
		$fileExtracted = getFileList($fileExtracted[0]);
		
	sort($fileExtracted);
		for ($count = 0 ; $count < count($fileExtracted); $count++) {
	        $fileArray = $fileExtracted[$count];
	 
	        if (!is_dir($fileArray) && $fileArray != "." && $fileArray != "..") {
	            $baseFileName = str_replace($extractFolder,'',$fileArray);
				
	          $db->page_create('',$count,$baseFileName,$pubdate,'',$count,$_POST['series_id'],$chapterID,'');
	        } 
		}
		$status['pass'] = "The rarred chapter has been dumped.";
		rar_close($rar_file);
} else {
		if ($chapter['fail']['number']['duplicate']) $status['error'] .= 'The Chapter number has already been taken.<br/>';
		if ($chapter['fail']['number']['character']) $status['error'] .= 'The Chapter number has to be in numbers.<br/>';
		if ($chapter['fail']['scanlator']) $status['error'] .= 'The Scanlator does not exist<br/>';
		if ($chapter['fail']['slug']) $status['error'] .= 'The slug already exist<br/>';
}
?>
