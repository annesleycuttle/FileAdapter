~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
COMPARISON

thePHPLeague\flysystem compared with FabricDigital\flysystem
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
src : https://github.com/thephpleague/flysystem/
src : https://github.com/FabricDigital/FileAdapter/

This document serves to explain any differences between the PHP league flystem adapters
and our adapters. We chose to create our own because our systems run on php 5.3.3 which is not
compatible with thePHPLeague\flysystem. We have tried to imitate this library as close as possible
to gain the benefits of file system adapter whilst also keeping implementation close to a well known
library. I will now refer to thePHPLeague\flysystem as PL for shorthand.

The main topics are :

Interfaces
Main Class - Filesystem
Adpaters
Exceptions
Misc
Notes for Improvement

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Interfaces
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
with in PL there are 3 main interfaces :

AdapterInferface
ReadInterface
FilesystemInterface

We have chosen to not create FilesystemInterface but just create the implementation class Filesystem 
soley. PL user AdapterInferface and ReadInterface to define the interfaces for its adapters. We have chosen
to merge them into one interface called AdapterInferface. The following is a list of all PL adapter functions and
the ones we have included :

- function_name -> implemented (yes/no)

ReadInterface
- has -> yes
- read -> yes
- readStream -> no
- listcontents -> yes
- getMetadata -> no
- getSize -> yes
- getMimetype -> yes
- getTimestamp -> yes
- getVisibility -> Yes (renamed : getPermissions, return int permission not public/private )

AdapterInferface
- write -> yes
- writeStream -> no
- update -> no
- updateStream -> no
- rename -> no
- copy -> yes
- delete -> yes
- deleteDir -> yes
- createDir -> yes
- setVisibility -> yes (renamed : setPermissions, takes octal permission not public/private )


~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Main Class - filesystem
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PL uses traits, we don't
PL uses InvalidArgumentException we do not. only used for stream functions, which we haven't implemented
PL uses RootViolationException, we do not. thrown if delete param is empty, assumed user trying to delete the root dir

Function list 
- PL function_name -> implemented (yes/no)
- __construct -> yes (works the same)
- getAdpater -> yes
- has -> yes
- write-> yes
- writeStream -> no
- put -> no
- putStream -> no
- readAndDelete -> no
- update -> no
- updateStream -> no
- read -> yes
- readStream -> no
- rename -> no
- copy -> yes
- delete -> yes
- deleteDir -> yes
- createDir -> yes
- listContents -> yes
- getMimetype -> yes
- getTimestamp -> yes
- getVisibility -> Yes (renamed : getPermissions, return int permission not public/private )
- getSize -> yes
- setVisibility -> yes (renamed : setPermissions, takes octal permission not public/private )
- getMetadata -> no
- get -> no
- assertPresent -> no
- assertAbsent -> no


~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Adapters
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Pl has implemented adapters for most common file systems including :
- local
- zipArchive
- ftp
- ftpd
- synologyFtp
- NullAdapter (seems to be empty dummy functions)
- Dropbox
- AWS
- Azure
- SFTP
- Rackspace
and many more

We have implemented :
- local 
- ZipArchive (work in progress)
- s3 (replacement for AWS, work in progress)

in the interface section you can see a list of functions not implemented. This section will just discuss the differences
between the local adapters.

PL local has the extra ability to :
-  define write flag LOCK_EX on the file_put_contents functions
- when deleting a dir it will check for and throw UnreadableFileException if needed
- handles links (assumed sys links  / aliases ?)
- doesn't handle files not owned by the web user
- some function return meta data arrays rather than true/false

default file permission for PL :
    protected static $permissions = [
        'file' => [
            'public' => 0644,
            'private' => 0600,
        ],
        'dir' => [
            'public' => 0755,
            'private' => 0700,
        ]
    ];

    we structure it like this :

    private $default_permisons = array(
        'file'=>0644,
        'folder'=>=755
    );


~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Exceptions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PL throws the following :
FileExistsException
FileNotFoundException
NotSupportedException
RootViolationException
UnreadableFileException

We Throw :
FileExistsException
FileNotFoundException


~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Misc
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PL supports :
- plugins
- handlers (file or directory)
- config objects
- seems to have a MountManager


~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Notes for Improvement
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
- Add RootViolationException to local adapter/ filesystem used in deleteDir
- look into the stream functions and implementing them
- function getMetadata, useful. seems like PL implementation could be better and have more info
- add write flags to file_put_contents
- suggest we write a file append function (doesn't exist in PL)