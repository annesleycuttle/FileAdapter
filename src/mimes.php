<?php  
/*
| -------------------------------------------------------------------
| MIME TYPES
| -------------------------------------------------------------------
| This file contains an array of mime types.  It is used by the
| Upload class to help identify allowed file types.
|
*/
global $mimes;
$mimes = array(	'hqx'	=>	array('application/mac-binhex40'),
				'cpt'	=>	array('application/mac-compactpro',
				'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
				'bin'	=>	array('application/macbinary'),
				'dms'	=>	array('application/octet-stream'),
				'lha'	=>	array('application/octet-stream'),
				'lzh'	=>	array('application/octet-stream'),
				'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
				'class'	=>	array('application/octet-stream'),
				'psd'	=>	array('application/x-photoshop'),
				'so'	=>	array('application/octet-stream'),
				'sea'	=>	array('application/octet-stream'),
				'dll'	=>	array('application/octet-stream'),
				'oda'	=>	array('application/oda'),
				'pdf'	=>	array('application/pdf', 'application/x-download'),
				'ai'	=>	array('application/postscript'),
				'eps'	=>	array('application/postscript'),
				'ps'	=>	array('application/postscript'),
				'smi'	=>	array('application/smil'),
				'smil'	=>	array('application/smil'),
				'mif'	=>	array('application/vnd.mif'),
				'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
				'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
				'wbxml'	=>	array('application/wbxml'),
				'wmlc'	=>	array('application/wmlc'),
				'dcr'	=>	array('application/x-director'),
				'dir'	=>	array('application/x-director'),
				'dxr'	=>	array('application/x-director'),
				'dvi'	=>	array('application/x-dvi'),
				'gtar'	=>	array('application/x-gtar'),
				'gz'	=>	array('application/x-gzip'),
				'php'	=>	array('application/x-httpd-php'),
				'php4'	=>	array('application/x-httpd-php'),
				'php3'	=>	array('application/x-httpd-php'),
				'phtml'	=>	array('application/x-httpd-php'),
				'phps'	=>	array('application/x-httpd-php-source'),
				'js'	=>	array('application/x-javascript'),
				'swf'	=>	array('application/x-shockwave-flash'),
				'sit'	=>	array('application/x-stuffit'),
				'tar'	=>	array('application/x-tar'),
				'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
				'xhtml'	=>	array('application/xhtml+xml'),
				'xht'	=>	array('application/xhtml+xml'),
				'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
				'mid'	=>	array('audio/midi'),
				'midi'	=>	array('audio/midi'),
				'mpga'	=>	array('audio/mpeg'),
				'mp2'	=>	array('audio/mpeg'),
				'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
				'aif'	=>	array('audio/x-aiff'),
				'aiff'	=>	array('audio/x-aiff'),
				'aifc'	=>	array('audio/x-aiff'),
				'ram'	=>	array('audio/x-pn-realaudio'),
				'rm'	=>	array('audio/x-pn-realaudio'),
				'rpm'	=>	array('audio/x-pn-realaudio-plugin'),
				'ra'	=>	array('audio/x-realaudio'),
				'rv'	=>	array('video/vnd.rn-realvideo'),
				'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
				'bmp'	=>	array('image/bmp', 'image/x-windows-bmp'),
				'gif'	=>	array('image/gif'),
				'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
				'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
				'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
				'png'	=>	array('image/png',  'image/x-png'),
				'tiff'	=>	array('image/tiff'),
				'tif'	=>	array('image/tiff'),
				'css'	=>	array('text/css'),
				'html'	=>	array('text/html'),
				'htm'	=>	array('text/html'),
				'shtml'	=>	array('text/html'),
				'txt'	=>	array('text/plain'),
				'text'	=>	array('text/plain'),
				'log'	=>	array('text/plain', 'text/x-log'),
				'rtx'	=>	array('text/richtext'),
				'rtf'	=>	array('text/rtf'),
				'xml'	=>	array('text/xml'),
				'xsl'	=>	array('text/xml'),
				'mpeg'	=>	array('video/mpeg'),
				'mpg'	=>	array('video/mpeg'),
				'mpe'	=>	array('video/mpeg'),
				'qt'	=>	array('video/quicktime'),
				'mov'	=>	array('video/quicktime'),
				'avi'	=>	array('video/x-msvideo'),
				'movie'	=>	array('video/x-sgi-movie'),
				'doc'	=>	array('application/msword'),
				'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
				'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'),
				'word'	=>	array('application/msword', 'application/octet-stream'),
				'xl'	=>	array('application/excel'),
				'eml'	=>	array('message/rfc822'),
				'json' => array('application/json', 'text/json')
			);


/* End of file mimes.php */
/* Location: ./application/config/mimes.php */
