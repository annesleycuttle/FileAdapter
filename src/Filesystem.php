<?php
/**
 *  Class: Filesystem
 *  ~~~~~~~~~~~~~~~~~~~~~
 *  Author : mike.bamber 
 *  Date: 17/03/2016
 *
 * The purpose of this class is to imitate the functionality of the public interface 
 * called Flystem. Unfortunately we can not use flysystem as this system currently 
 * only supports 5.3.3 and flystem requires 5.5.0 when using the AMZ adapater.
 *
 * The hope, by imitating function names and class names, we could move over to Flysystem
 * in future in a smoother fashion, should we upgrade the supported PHP version.
 *    #theJoysOfLegacySoftware
 */
// who needs an autoloader when you can include them all manually and painstakingly 
#include __DIR__ . '/adapter_interface.php';
#include __DIR__ . '/exceptions/file_exists.php';
#include __DIR__ . '/exceptions/file_not_found.php';

namespace FileAdapter;
use ZipArchive;

class Filesystem {

	protected $adapter = null;
	private $tmp_dir ;
	private $ext_mime_map = array(
		
	);

	public function __construct( Adapters\AdapterInterface $adapter ){
		global $mimes;
		$this->ext_mime_map = $mime;
		$this->adapter = $adapter;
		if (!file_exists('/tmp/FileAdapter')) {
			mkdir('/tmp/FileAdapter', 0777);
		}
		$this->tmp_dir = '/tmp/FileAdapter';
	}
	/**
	 * return the adapter in user
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @return AdapterInterface $adapter
	 */
	public function getAdapter(){
		return $this->adapter;
	}
	public function getPathPrefix(){
		return $this->getAdapter()->getPathPrefix();
		
	}
	/**
	 * Check if a file exists on the file system
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path 
	 * @return boolean          
	 */
	public function has($path){
		return $this->getAdapter()->has($path);
	}
	/**
	 * Write a string to a given path on the file system
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path     -> the file path of the new file
	 * @param  string     $contents -> the contents to save
	 * @param  array     $config -> any optional config
	 *
	 * @throws FileExistsException
	 * 
	 * @return bool
	 */
	public function write($path , $contents, array $config = array() ){
		return $this->getAdapter()->write( $path , $contents , $config );
	}	
	/**
	 * Update the contents of a file on the file systems
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path     -> the file path of the existing file
	 * @param  string     $contents -> the contents to save
	 * @param  array     $config -> any optional config
	 *
	 * @throws FileNotFoundException
	 * 
	 * @return bool
	 */
	public function update($path , $contents, array $config = array() ){
		return $this->getAdapter()->update( $path , $contents , $config );
	}
	/**
	 * Create a file or update if exists then write contents
	 * @author mike.bamber
	 * @date   2016-06-21
	 * @param  string     $path     -> the file path of the existing file
	 * @param  string     $contents -> the contents to save
	 * @param  array     $config -> any optional config
	 * @return bool
	 */
	public function put($path , $contents, array $config = array()){

		if( $this->has($path) ){
			return $this->update($path , $contents , $config);
		}else{
			return $this->write($path , $contents , $config);
		}
	}
	/**
	 * rename and or move a file on the file system from one location
	 * to another location
	 * @author mike.bamber
	 * @date   2016-06-21
	 * @param  string     $path     -> the file path of the existing file
	 * @param  string     $newpath  -> the file path of new location and filename
	 * @return boolean            
	 */
	public function rename($path, $newpath){
		return $this->getAdapter()->rename( $path , $newpath );
	}
	/**
	 * Read a file and download to users browser by streaming
	 * file contents
	 * @author mike.bamber
	 * @date   2016-05-12
	 * @param  string     $path -> pat to an existing path
	 * @return header()
	 */
	public function download($path){

		header('Pragma: public');
		header('Expires: 0');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/octet-stream');

		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		$filename = basename($path);
		header('Content-Disposition: attachment; filename="' . $filename . '";');

		$string = $this->getAdapter()->read($path);

		// Stream the file data
		exit($string);
	}
	/**
	 * Zip a file/folder on the atached storage, save zip to 
	 * temp directory and return location of file in tmp directory
	 * @author mike.bamber
	 * @date   2016-06-20
	 * @param  string     $adapter_source_path -> path to file/folder on storage to zip
	 * @return string/false -> tmp file path of newly created zip file, false if failed
	 */
	public function zip($adapter_source_path){
	
		$success = false;
		$files_to_zip = array();
		$zippy = new ZipArchive();
		$tmp_file_path = $this->tmp_dir. DIRECTORY_SEPARATOR . mktime() . rand(1,1000).'.zip';

		if($zippy->open($tmp_file_path, ZIPARCHIVE::CREATE)){

			// if ifs a directory then zip up else just zip the file 
			if( $this->getAdapter()->is_dir($adapter_source_path) ){

				// user the connected adapter to retreive a list of the folder contents
				$files  = $this->getAdapter()->listContents( $adapter_source_path , true );

				// lets recursively roll over folder and add them into the zip archive
				$this->addFolderToZip( $adapter_source_path , $files , $zippy );

				
			}else{
				$zippy->addFromString(basename($adapter_source_path) , $this->getAdapter()->read($adapter_source_path) );
			}

			// if we can close and the zip is there, presume success and return the tmp location
			if($zippy->close()){

				if(file_exists($tmp_file_path)){
					$success = $tmp_file_path;
				}

			}
		}

		return $success;	

	}
	/**
	 * recurse directory as array of file paths and folders, adding whole
	 * structure to a the zipArchive object passed in
	 * @author mike.bamber
	 * @date   2016-06-20
	 * @param  string     $source_path    -> source path of the directory
	 * @param  array     $paths          -> array or file and folder paths
	 * @param  ZipArchive &$zipArchiveObj -> ZipArchive object to add files too
	 */
	private function addFolderToZip( $source_path , $paths , ZipArchive &$zipArchiveObj ){

		foreach($paths as $item){

			if( is_array($item) ){
				$this->addFolderToZip( $source_path , $item , $zipArchiveObj );
			}else{
				// if we are zipping a folder then we dont want the internal zip structure to reflect its external location
				// treat $source_path as the root for the zip
				$x = str_replace($source_path, '', $item);
				// zipArchive doesnt like slashes at the begining of file paths when defining its internal location
				$new_path =  ltrim($x, DIRECTORY_SEPARATOR);

				$zipArchiveObj->addFromString( $new_path , $this->getAdapter()->read($item) );

			}
		}
	}
	/**
	 * Zip a file/folder on the attached storage and download to the browser
	 * @author mike.bamber
	 * @date   2016-06-20
	 * @param  string     $adapter_source_path -> path to file/folder on storage to zip
	 * @param  string/boolean    $filename   -> if string then will be used to name downloaded zip       
	 * @return  header() -> downloaded zip file
	 */
	public function zipAndDownload($adapter_source_path , $filename = false ){

		$tmp_file_path = $this->zip($adapter_source_path);

		$filename = $this->setUpZipFileName($adapter_source_path , $filename);
		
		header('Content-type: application/zip');	
		header('Content-Disposition: attachment; filename="' . $filename . '";');
		
		readfile($tmp_file_path,true);
		$this->zipFlushTmp($tmp_file_path);
		exit;
	}
	/**
	 * Zip a file/folder on the attached storage and save to elsewhere on the attached storage
	 * @author mike.bamber
	 * @date   2016-06-21
	 * @param  string     $adapter_source_path -> path to file/folder on storage to zip
	 * @param  string     $destination_path    -> folder to save the zip folder to
	 * @param  string/boolean    $filename   -> if string then will be used to name save zip, if false uses basename
	 * @return boolean                         
	 */
	public function zipAndSave($adapter_source_path , $destination_path , $filename = false ){

		$tmp_file_path = $this->zip($adapter_source_path);

		$filename = $this->setUpZipFileName($adapter_source_path , $filename);

		$zipContents = file_get_contents($tmp_file_path);

		return $this->getAdapter()->write( $destination_path . DIRECTORY_SEPARATOR . $filename , $zipContents  );
	}
	/**
	 *  Take input and prepare filename for a zip file
	 * @author mike.bamber
	 * @date   2016-06-21
	 * @param  boolean    $filename [description]
	 */
	private function setUpZipFileName( $adapter_source_path , $filename = false){
		// if filename has not been overriden then set it
		if( !is_string($filename) ){
			// lets build the filename
			$filename = basename($adapter_source_path);
			$ext = pathinfo( $adapter_source_path , PATHINFO_EXTENSION);
			// if its a file, we need to replace the ext with zip for file name
			if(!empty($ext)){
				$filename = str_replace($ext, 'zip', $filename);
			}
		}
		// if the passed in variable does not have the zip extension or its a folder then add the .zip on the end
		if( strpos($filename, '.zip') === false ){
			$filename .= '.zip';
		}
		return $filename;
	}
	/**
	 * Remove the temp file for a zip file that has bee created
	 * @author mike.bamber
	 * @date   2016-06-20
	 * @param  string     $tmp_file_path -> path to tmp file
	 * @return null
	 */
	public function zipFlushTmp($tmp_file_path){
		if( file_exists($tmp_file_path) ){
			unlink($tmp_file_path);
		}
		
	}
	 /**
	 * Read a file.
	 *
	 * @param string $path The path to the file.
	 *
	 * @throws FileNotFoundException
	 *
	 * @return string|false The file contents or false on failure.
	 */
	public function read($path){
		return $this->getAdapter()->read($path);
	}	
	/**
	 * Read a file and return file resource object.
	 *
	 * @param string $path The path to the file.
	 *
	 * @throws FileNotFoundException
	 *
	 * @return Object|false A PHP file pointer object/resource.
	 */
	public function readStream($path){
		return $this->getAdapter()->readStream($path);
	}	 
	/**
	 * Delete a file.
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param string $path The path to the file.
	 *
	 * @throws FileNotFoundException
	 *
	 * @return bool
	 */
	public function delete($path){
		return $this->getAdapter()->delete($path);
	}
	/**
	 * Read the contents of a file and return as a string
	 * deleting file from the disk
	 * @author mike.bamber
	 * @date   2016-06-21
	 * @param  string     $path ->  The path to the file.
	 *
	 * @throws FileNotFoundException
	 *  
	 * @return string|false The file contents or false on failure.
	 */
	public function readAndDelete($path){

		$data =  $this->getAdapter()->read($path);
		if($data){
			$this->getAdapter()->delete($path);
		}
		return $data;
	}
	/**
	 * Copy a file from one location to another
	 * @author mike.bamber
	 * @date   2016-03-18
	 * @param  string     $path    -> /place/to/move/from/file.txt
	 * @param  string     $newpath -> /place/to/go/
	 * @return bool True on success, false on failure.
	 */
	public function copy($path, $newpath){
		return $this->getAdapter()->copy( $path , $newpath );
	}

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory The directory to list.
	 * @param bool   $recursive Whether to list recursively.
	 *
	 * @return array A list of file metadata.
	 */
	public function listContents($directory = '', $recursive = false){
		return $this->getAdapter()->listContents( $directory , $recursive );
	}
	/**
	 * Create a directory on the file system
	 * @author mike.bamber
	 * @date   2016-03-18
	 * @param  string     $directory  -> the full directory path to create e.g. /some/folder/newFolderName
	 * @return  bool -> succes/fail
	 */
	public function createDir($directory){
		return $this->getAdapter()->createDir( $directory );
	}	
	/**
	 * Recursively delete all files and folders within this directory,
 	 * then remove directory itself
	 * @author mike.bamber
	 * @date   2016-03-18
	 * @param  string     $directory  -> the full directory path to delete e.g. /some/folder/folderToDelete
	 * @return  bool -> succes/fail
	 */
	public function deleteDir($directory){
		return $this->getAdapter()->deleteDir( $directory );
	}

	/**
	 * Gets the size of a given file
	 * @author  fin.cardy
	 * @param  String $path  - Path to file 
	 * @return String        - Size of file
	 */
	public function getSize($path){
		return $this->getAdapter()->getSize( $path );
	}

	/**
	 * Gets the MIME Type of a given file
	 * @author  fin.cardy
	 * @param  String $path  - Path to file 
	 * @return String        - MIME Type of File
	 */
	public function getMimetype($path){
		return $this->getAdapter()->getMimetype( $path );
	}

	/**
	 * Gets the MIME Type of a given file
	 * @author  fin.cardy
	 * @param  String $path  - Path to file 
	 * @return String        - MIME Type of File
	 */
	public function getTimestamp($path){
		return $this->getAdapter()->getTimestamp( $path );
	}
	/**
	 * Check if the provided path is of a given file type(s) specifed 
	 * by the provided list
	 * @param  string  $path  -> path to the file
	 * @param  array   $types -> array of string file extension types, e.g. ('csv','txt','pdf')
	 * @return boolean        
	 */
	public function isType($path, $types = array()){

		$mime = $this->getAdapter()->getMimetype( $path );
		$bits = explode('.', $path);
		$ext = $bits[ count($bits)-1 ];
		$mimeCheckPassed = false;
		$extCheckPassed = false;
		foreach($types as $type){
			
			if( in_array( $mime , $this->ext_mime_map[ $type ] ) ){
				$mimeCheckPassed = true;
			}
			if($ext == $type){
				$extCheckPassed = true;		
			}
			
		}
		if($mimeCheckPassed && $extCheckPassed){
			return true;
		}else{
			return false;
		}

	}
	/**
	 * Given a file path and a memory reference string, it will 
	 * return true if the file size is under the passed reference
	 * @param  string $path         -> path to the file
	 * @param  string/int $mixed_memory -> int : bytes / string memory ref, e.g. 54mb|34kb|4gb (case in-sensitive)
	 * @return boolean               
	 */
	public function checkFilesize($path, $mixed_memory){
		$bytes = $this->convertToBytes($mixed_memory);
		$size = $this->getSize($path);
		return ($size <= $bytes);
	}
	/**
	 * Gets the permisions for  of a given file/folder
	 * @param  String $path  - Path to file 
	 */
	public function getPermissions($path){
		return $this->getAdapter()->getPermissions( $path );
	}
	/**
	 * Gets the permisions for  of a given file/folder
	 * @param  String $path  - Path to file 
	 */
	public function setPermissions( $path , $permisions ){
		return $this->getAdapter()->setPermissions( $path , $permisions );
	}
	/**
	 * Given a memory reference string, it will 
	 * return the integer value of bytes for the passed ref 
	 * @param  string/int $mixed_memory -> int : bytes / string memory ref, e.g. 54mb|34kb|4gb (case in-sensitive)
	 * @return boolean               
	 */
	private function convertToBytes($mixed_memory){

		$bytes = 0;
		$units = array( 'TB'=>4, 'GB'=>3, 'MB'=>2, 'KB'=>1, 'B'=>0 );
	
		if( is_numeric( $mixed_memory ) ){
			$bytes = (int)$mixed_memory;
		}else{
			foreach($units as $unit => $scale){
				$m = strtoupper($mixed_memory);
				if(strrpos( $m , $unit )){
					$mem_amount = str_replace($unit, '', $m);
					for ($i = 0; $i < $scale; $i++){
						$mem_amount = $mem_amount  * 1024;
					}
					$bytes = $mem_amount;
					break;
				}
			}
		}
		return $bytes;
	}


}
