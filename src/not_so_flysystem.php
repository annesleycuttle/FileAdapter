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

class Filesystem {

	protected $adapter = null;

	public function __construct( AdapterInterface $adapter ){
		$this->adapter = $adapter;
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

}