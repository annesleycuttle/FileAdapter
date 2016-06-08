<?php
namespace FileAdapter\Adapters;

interface AdapterInterface {

	 /**
	     * Write a new file to file system.
	     *
	     * @param string $path
	     * @param string $contents
	     *  @param  array     $config -> any optional config
	     *
	     * @throws FileExistsException
	     *
	     * @return array|false false on failure file meta data on success
	     */
	public function write($path , $contents , array $config = array() );
	/*
	     * Copy file to another place on the file system.
	     *
	     * @param string $path
	     * @param string $new_path
	     *
	     * @throws FileNotFoundException
	     * @throws FileExistsException
	     *
	     * @return array|false false on failure file meta data on success
	     */
	public function copy($current_path , $new_path );

	 /**
	     * Check whether a file exists.
	     *
	     * @param string $path
	     * 
	     * @return bool
	     */
	 public function has($path) ;
	/**
	* Read a file.
	*
	* @param string $path
	*
	* @throws FileNotFoundException
	*
	* @return string|false -> contents of file on success else false
	*/
	 public function read($path);	
	 /**
	* Delete a file.
	*
	* @param string $path
	*
	* @throws FileNotFoundException
	*
	* @return bool
	*/
	 public function delete($path);
	/**
	 * List contents of a directory.
	 *
	 * @param string $directory
	 * @param bool   $recursive
	 *
	 * @return array
	 */
	public function listContents($directory = '', $recursive = false);
	/**
	 * Create a directory.
	 *
	 * @param string $dirname directory name
	 * @param Config $config
	 *
	 * @return true|false
	 */
	public function createDir($dirname);	
	/**
	 * Delete a directory.
	 *
	 * @param string $dirname directory name
	 * @param Config $config
	 *
	 * @return true|false
	 */
	public function deleteDir($dirname);
	/**
	 * Get all the meta data of a file or directory.
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public function getSize($path);
	/**
	 * Get the mimetype of a file.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function getMimetype($path);
	/**
	 * Get the timestamp of a file.
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public function getTimestamp($path);

	/**
	 * Set the permissions for a file.
	 *
	 * @param string $path
	 * @param string $permissions 
	 *
	 * @return boolean
	 */
	public function setPermissions($path, $permissions);	
	/**
	 * Get the permissions for a file.
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public function getPermissions($path);
}