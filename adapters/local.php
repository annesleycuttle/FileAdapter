<?php

class Local implements AdapterInterface{

	private $root_path = '';

	public function __construct( $root = '' ){
		$this->root_path = $root;
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

			return (bool)file_put_contents($full_path, $contents);	

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

		return (bool)$this->ensureDirectory( $full_path );
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
	 * @return int           
	 */
	public function getSize($path){

	}
	/**
	 * return the files mime type
	 * @author mike.bamber
	 * @date   2016-03-17
	 * @param  string     $path
	 * @return string
	 */
	public function getMimetype($path){

	}
	/**
	 * retreive the timestamp of the file
	 * @author mike.bamber
	 * @date   2016-03-17
	  * @param  string     $path
	 * @return string
	 */
	public function getTimestamp($path){

	}
}