<?php
namespace FileAdapter\Exceptions;

use \Exception;

class FileExistsException extends Exception
{
    /**
     * @var string
     */
    protected $path;
    /**
     * Constructor.
     *
     * @param string        $path
     * @param int           $code
     * @param BaseException $previous
     */
    public function __construct($path, $code = 0, Exception $previous = null)
    {
        $this->path = $path;
        parent::__construct('File already exists at path: ' . $this->getPath(), $code, $previous);
    }
    /**
     * Get the path which was not found.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}