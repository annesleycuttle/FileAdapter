<?php
namespace FileAdapter\Adapters;

use FileAdapter\Exceptions\FileNotFoundException;
use FileAdapter\Exceptions\FileExistsException;
use finfo;

class Local implements AdapterInterface{

	private $root_path = '';

	private $writeFlags;

	private $default_permisons;

	public function __construct( $root = '', $writeFlags = LOCK_EX, $permissions = array() ){

		$this->default_permisons =  array(
			'file'=>0644,
			'folder'=>0755
		);

		$this->root_path = $root;
		$this->writeFlags = $writeFlags;
		if(isset($permissions['file'])){
			$this->default_permisons['file'] = $permissions['file'];
		}		
		if(isset($permissions['folder'])){
			$this->default_permisons['folder'] = $permissions['folder'];
		}
	}
	/**
	 * write this file to the local HDD
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path     
	 * @param  string     $contents 
	 * @param  array      $config   
	 *
	  * @throws FileExistsException
	  * 
	 * @return bool               
	 */
	public function write($path , $contents, array $config = array() ){

		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;

		$this->ensureDirectory( dirname($full_path) );

		if( !$this->has($path) ){

			$x = (bool)file_put_contents($full_path, $contents);
			$this->setPermissions($path,$this->default_permisons['file']);	
			return $x;

		}else{
			throw new FileExistsException($full_path );
		}
	}
	/*
	     * Copy file to another place on the file system.
	     *
	     * @param string $path -> curent location of file to be copied
	     * @param string $new_path -> should be the target directory to copy file to
	     *
	     * @throws FileNotFoundException
	     * @throws FileExistsException
	     *
	     * @return array|false false on failure file meta data on success
	     */
	public function copy($current_path , $new_dir ){

		$current_full_path = $this->root_path . DIRECTORY_SEPARATOR . $current_path;
		
		$new_full_dir = $this->root_path . DIRECTORY_SEPARATOR . $new_dir;
		$new_path = $new_dir . DIRECTORY_SEPARATOR . basename($current_full_path);
		$new_full_path = $new_full_dir . DIRECTORY_SEPARATOR . basename($current_full_path);

		if( $this->has($current_path) ){

			if( !$this->has($new_path) ){
				// make sure we have the directory to save to
				$this->ensureDirectory($new_full_dir);
				return copy( $current_full_path , $new_full_path );
			}else{
				throw new FileExistsException($new_full_path );
			}

		}else{
			throw new FileNotFoundException($current_full_path );
		}
	}
	/**
	* Ensure the root directory exists.
	*
	* @param string $root root directory path
	*
	* @return string real path to root
	*/
	protected function ensureDirectory($root)
	{
		if ( ! is_dir($root)) {
			$umask = umask(0);
			mkdir($root, 0777 , true);
			umask($umask);
		}
		return realpath($root);
	}
	/**
	 * Read a file, returning its string contents
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path 
	 *
	  * @throws FileNotFoundException
	  * 
	 * @return string/false -> based on sucess
	 */
	public function read($path){

		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;

		if( $this->has($path) ){

			return  file_get_contents($full_path);	

		}else{
			throw new FileNotFoundException($full_path );
		}
	}
	/**
	 * remove a file from the file storage
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path 
	 *
	 *@throws FileNotFoundException
	 *
	 * @return bool
	 */
	public function delete($path){

		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;

		if( $this->has($path) ){

			return  unlink($full_path);	

		}else{
			throw new FileNotFoundException($full_path );
		}
	}
	/**
	 *  Check the file storage to see if the file exists
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path
	 * @return boolean          
	 */
	public function has($path){

		return file_exists( $this->root_path . DIRECTORY_SEPARATOR . $path );
	}
	/**
	 * List the contents of the directory
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $directory -> path/to/folder
	 * @param  boolean    $recursive -> whether to return a recursive array of lower folders
	 * @return array/false -> array contents if a directory, false if not a directory
	 */
	public function listContents($directory = '', $recursive = false){
		return $this->process_folder($directory);
	}
	/**
	 * [process_folder description]
	 * @author mike.bamber
	 * @date   2016-06-08
	 * @param  [type]     $folder_path [description]
	 * @return [type]                  [description]
	 */
	private function process_folder($folder_path){

		$directory = array();

		$folder = scandir($folder_path);

		foreach( $folder as $item ){

			// if we are not the default ./ or ../ then process the cotents
			if( !in_array($item, array('.','..') ) ){
				$item_path =  $folder_path.DS. $item;
				if(is_dir( $item_path )){
			
					$directory[ $item] = $this->process_folder( $item_path );

				}else{
		
					$directory[$item] =  $item_path;
				}	
			}


		}
		return  $directory;
	}
	/**
	 * Create a directory on the file system
	 * @author mike.bamber
	 * @date   2016-03-18
	 * @param  string     $dirname  -> the full directory path to create e.g. /some/folder/newFolderName
	 * @return  bool -> succes/fail
	 */
 	public function createDir($dirname){
		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $dirname;

		$x = (bool)$this->ensureDirectory( $full_path );
		$this->setPermissions($dirname,$this->default_permisons['folder']);
		return $x;
 	}
 	/**
 	 * Recursively delete all files and folders within this directory,
 	 * then remove directory itself
 	 * @author mike.bamber
 	 * @date   2016-03-18
 	 * @param  string     $dirname [description]
 	 *  @return  bool -> succes/fail
 	 */
 	public function deleteDir($dirname){

 		if($this->has($dirname)){
 		 	if($this->delete_files($dirname,true)){
	 			return rmdir($dirname);
	 		}else{
	 			return false;
	 		}	
 		}else{
 			return false;
 		}



 	}
 	/**
 	 * function for recursing down a directory deleting everything
 	 * function  copied from the code ignitor file_helper
 	 * @author mike.bamber
 	 * @date   2016-03-18
 	 * @param  string     $path    -> path to delete
 	 * @param  boolean    $del_dir whether to recurse down into sub folders
 	 * @param  integer    $level  -> internal function for managing traversal
 	 * @return true/false -> success/fail
 	 */
 	private function delete_files($path, $del_dir = FALSE, $level = 0)
	{
		// Trim the trailing slash
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		if ( ! $current_dir = @opendir($path))
		{
			return FALSE;
		}

		while (FALSE !== ($filename = @readdir($current_dir)))
		{
			if ($filename != "." and $filename != "..")
			{
				if (is_dir($path.DIRECTORY_SEPARATOR.$filename))
				{
					// Ignore empty folders
					if (substr($filename, 0, 1) != '.')
					{
						$this->delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
					}
				}
				else
				{
					unlink($path.DIRECTORY_SEPARATOR.$filename);
				}
			}
		}
		@closedir($current_dir);

		if ($del_dir == TRUE AND $level > 0)
		{
			return @rmdir($path);
		}

		return TRUE;
	}
	/**
	 * return size in bytes of the file
	 * @author mike.bamber
	 * @date   2016-03-17
	  * @param  string     $path
	 * @return int (bytes)          
	 */
	public function getSize($path){

		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;
		if( $this->has($path) ){

			return filesize($full_path);

		}else{
			throw new FileNotFoundException($full_path );
		}
		
	}
	/**
	 * return the files mime type
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path
	 * @return string
	 */
	public function getMimetype($path){

		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;
		if( $this->has($path) ){

			$finfo = new finfo(FILEINFO_MIME_TYPE);
			return $finfo->file($full_path);

		}else{
			throw new FileNotFoundException($full_path );
		}
	}
	/**
	 * retreive the timestamp of the file
	 * @author mike.bamber
	 * @date   2016-03-17
	  * @param  string     $path
	 * @return int (unix timestamp)
	 */
	public function getTimestamp($path){
		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;
		if( $this->has($path) ){

			return filemtime ($full_path);

		}else{
			throw new FileNotFoundException($full_path );
		}
	}
	/**
	 * Set the permissions on a folder/file
	 * @author mike.bamber
	 * @date   2016-06-08
	 * @param  string     $path        
	 * @param  octal     $permissions -> mustl be octal e.g. 0755. not 755 or '755'
	 */
	public function setPermissions( $path , $permissions ){
		$ret = false;
		if( $this->has($path) ){
			if(is_numeric($permissions) && !is_string($permissions)){
				$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;
				$ret = chmod( $full_path , $permissions );	
			}
		}else{
			throw new FileNotFoundException($full_path );
		}
		
		return $ret ;
	}
	/**
	 * Retrieve the file permissions of a folder in the numeric form
	 *  e.g. 0755. This will be return as a string number
	 * @author mike.bamber
	 * @date   2016-06-08
	 * @param  string     $path 
	 * @return string         permissions
	 */
	public function getPermissions( $path ){
		$perms = '0000';
		$full_path = $this->root_path . DIRECTORY_SEPARATOR . $path;

		clearstatcache();
		$perms =  substr(sprintf('%o', fileperms($full_path)), -4);
		
		return $perms;
	}
}
