<?php 

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception as BaseException;

class AmazonS3 implements AdapterInterface{

	protected $s3Client;
	protected $bucket;
	protected $prefix;

	public function __construct($bucket,$prefix)
	{
		// set up the amazon client
		$this->s3Client = $this->getClient();
		$this->bucket = $bucket;
		$this->prefix = $prefix; ;
	}

	 /**
     * Write a new file to file system.
     *
     * @param string $path
     * @param string $contents
     * @param  array     $config -> any optional config
     * @throws FileExistsException
     * @return array|false false on failure file meta data on success
     */
	public function write($path , $contents , array $config = array() )
	{

		// @todo - Check file exists, throw exception
		$this->s3Client->putObject(array(
			'Bucket' => $this->bucket,
			'Key' => $this->prefix.$path,
			'Body' => $contents
		)); 
	}


	/**
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
	public function copy($current_path , $new_path)
	{

		$bucket = $this->bucket;
		$filename = $this->getFilenameFromPath($current_path);

		if($filename === false) { return false; }

		// @TODO: use Prepath instead...
		$new_path = $this->addSlash($new_path) . $filename;

		try{
			$result = $this->s3Client->copyObject(array(
				'Bucket' => $this->bucket,
				'Key'	=> $this->prefix.$new_path,
				'CopySource' => "{$this->bucket}/{$this->prefix}{$current_path}", 
			));
		} catch (Exception $e) {
			$e->getMessage();
			return false;
		}

		// If we've not caught an exception, then all went well
		return true;
	}

	/**
    * Check whether a file exists.
    *
    * @param string $path
    * @return bool
    */
	public function has($path)
	{
		// Adds the prefix and removes double slashes
		$path = $this->prepPath($path);

	 	// Create a new iterator
	 	$iterator = $this->s3Client->getIterator('ListObjects', array(
	 		'Bucket' => $this->bucket,
	 		// Prefix requires full path name of object
	 		'Prefix' => $path,
	 	));


	 	// Loop through - note: this uses PHP's inbuilt iterator, so be careful
	 	foreach($iterator as $object) {

	 		// If we find an object with the right "path" then we've won
	 		if($object['Key'] == $path) {
	 			return true;
	 		}
	 	}

	 	// Otherwise it ain't there
	 	return false;
	 }

	/**
	* Read a file.
	*
	* @param string $path
	* @throws FileNotFoundException
	* @return string|false -> contents of file on success else false
	*/
	public function read($path)
	{
		$this->s3Client->registerStreamWrapper();
		
		return file_get_contents("s3://{$this->bucket}/{$this->prefix}{$path}");
	}

	/**
	* Delete a file.
	*
	* @param string $path
	* @throws FileNotFoundException
	* @return bool
	*/
	public function delete($path)
	{
	 	$delete = $this->s3Client->deleteObject(array(
	 		'Bucket' => $this->bucket,
	 		'Key' => $this->prefix.$path,
	 	));

	 	// It deletes, or doesn't delete and fails silently
	 	return true;
	}

	/**
	 * List contents of a directory.
	 *
	 * @param string $directory
	 * @param bool   $recursive
	 *
	 * @return array
	 */
	public function listContents($directory = '', $recursive = false, $format = true)
	{
		// Call in the iterator to grab the files list
		$iterator = $this->s3Client->getIterator('ListObjects', array(
			'Bucket' => $this->bucket,
			'Prefix' => $this->prefix.$directory
		));

		// Convert the iterator to an array
		$files = iterator_to_array($iterator, true);
		// If we want the formatted version of the files
		if($format) {
			return $this->formatFolders($files, $directory);
		} else {
			return $files;
		}
	}

	/**
	 * Create a directory.
	 *
	 * @param string $dirname directory name
	 * @param Config $config
	 *
	 * @return true|false
	 */
	public function createDir($dirname)
	{

		// Quickest way to ensure trailing slash, 
		// remove any that exist and readd
		$dirname = rtrim($dirname, '/') . '/';
		
		// Write an "empty" file which creates a folder
		$this->write($dirname, '');

		return true;
	}
	
	/**
	 * Delete a directory.
	 *
	 * @param string $dirname directory name
	 * @param Config $config
	 *
	 * @return true|false
	 */
	public function deleteDir($dirname)
	{
		// Make sure we've got a trailing slash
		$dirname = rtrim($dirname, '/') . '/';

		// Get a list of the directory's objects
		$objectList = $this->listContents($dirname, true, false);

		// If there's no results, that's as good as having deleted it
		// @TODO: THROW EXPECTION
		if(empty($objectList)) { return false; }

		// initialize a new array
		$deleteObjects = array();

		// Build an array of 'Key' => [filename] for each object
		foreach($objectList as $object) {
			// NB: No need for the full key, 
			// because we're getting the list from amazon directly 
			// so it'll already have those
			$deleteObjects[] = array('Key' => $object['Key']);
		}

		// Perform the multiple delete request in 1 call
		$result = $this->s3Client->deleteObjects(array(
			'Bucket' => $this->bucket,
			'Objects' => $deleteObjects,
		));

		return true;
	}

	/**
	 * Get all the meta data of a file or directory.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getSize($path)
	{
		$object = $this->getObject($path);

		return $object['ContentLength'];
	}
	
	/**
	 * Get the mimetype of a file.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getMimetype($path)
	{
		$object = $this->getObject($path);

		return $object['ContentType'];
	}
	
	/**
	 * Get the timestamp of a file.
	 *
	 * @param string $path
	 *
	 * @return array|false
	 */
	public function getTimestamp($path)
	{
		$object = $this->getObject($path);
		return $object['LastModified'];
	}

	/**
	 * Sets up an S3 client object
	 * @return Object
	 */
	protected function getClient()
	{
		return S3Client::factory(array(
			'credentials' => array(
				'key' => getenv('AWS_ACCESS_KEY'),
				'secret' => getenv('AWS_SECRET_KEY'),
			)	
		));
	}

	/**
	 * Gets an object, useful for various operations
	 * @param  String $key Filename/path
	 * @return Array       Object Array
	 */
	public function getObject($key)
	{
		$result = $this->s3Client->getObject(array(
			'Bucket' => $this->bucket,
			'Key' => $this->prefix.$key,
		));

		return $result;
	}

	/**
	 * Turns the array of files and folders into a nested 
	 * associative array
	 * @param  Array  $files     - Object Array from amazon
	 * @param  String $directory - String name of directory we're searching
	 * @return Array 
	 */
	private function formatFolders($files, $directory)
	{
		// Set up a new array
		$structure = array();
		$directory = $this->prefix.$directory;
		/** 
		 * 	This is complicated as hell and I apologize, 
		 *  but it's the best way without a properly recursive function 
		 */
		foreach($files as $_file){

			// This should replace any keys that are listed in the directory argument
			// ie. Gets what is *inside* the folder
		 	$newKey = preg_replace('/^'. preg_quote($directory, '/') .'/', '', $_file['Key']);

		 	// $temp is now the structure array
			$temp = &$structure;

			// Save our old key
			$keyCache = '';

			// Explode on the filename (key as aws calls it)
			foreach(explode('/', $newKey) as $key){
				// Check our key's not empty
				if($key !== ''){
					// Create a new variable that's a pointer to 
					// the structure variable with the new key 
					// as an array key
					$temp = &$temp[$key];

					// Temp variable is now basically "nested" within 
					// the structure array, and then whatever it's parent folder is
				}

				// Cache our key name
				$keyCache = $key;
			}

			// If our filename ends in a / it's a directory 
			// and should be an empty array this iteration
			if(substr($_file['Key'], -1, 1) == '/'){
				$temp = array();
			} else {
				// Otherwise it's the name of the 
				// file from our keyCache
				$temp = str_replace($directory, '', $_file['Key']);
			}
		}

		return $structure;
	}
	/**
	 * Removes any slashes that exist at the end of the path
	 * and readds them
	 * @param  $name - Key name to change
	 * @return String
	 */
	private function addSlash($name)
	{
		return rtrim($name, '/') . '/';
	}

	private function getFilenameFromPath($path)
	{
		return basename($path);
	}

	private function prepPath($path)
	{
		# stops checking of directories so commenting out
		#$path = trim($path, '/');


		// adds the prefix to the path
		$path = $this->prefix.$path;

		// Removes double slashes
		return preg_replace('#/+#','/',$path);
	}

}
