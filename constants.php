<?php
// numbers in single operation
define('COUNT', 100); // Maximum # of items in a call. Default is 20. Max is 100.
define('MAXITEMS', 3);   // Maximum # of items, for debug

// File names & paths
define('INSTALLDIR',	 			'/usr/share/httpd/ojsStatistics/'); // just an example

define('OUTPATHSTAT',	 			INSTALLDIR.'out/stat/'); 
define('OUTPATHGEO',	 			INSTALLDIR.'out/geo/'); 
define('OUTPATHJS',	 				INSTALLDIR.'part2/src/'); 
define("T_LOG", 					INSTALLDIR.'log.txt');
define("COOKIE_FILE_PATH", 			INSTALLDIR.'out/cookie.txt');

// Code STARTPOINT if you want to rebuild all data files - it takes quite a lot of time.
// Usually the job does not need to run more than each month.
// For example, code STARTPOINT as follows:
define("STARTPOINT", "202001");

// Web constants 
// !! Mandatory!! The journal site in the form of zzz.xxx.yyy
define("T_URL1", 'https://www.nowhere.com/index.php/');
//define("T_URL1", 'https://<journalsite>/index.php/');
define("T_URL2", '/api/v1/');
// !! Mandatory!! One administrator user that can manage all journals. Just substitute <username> and <password> with yours
define("POSTINFO", "username=ronald&password=regan");
//define("POSTINFO", "username=<username>&password=<password>");
// !! Mandatory!! The login will proceed only when there is an action URL 
// The action URL is usually in the form <journalName>/login/signIn
define ("ACTIONURL", "journalname/login/signIn"); 
// !! Mandatory!! The API KEY 
define("T_APIK",  'apiToken=kjhkjhxkjkjhkhjjhkjhkjhbjhb');
//define("T_APIK", 'apiToken=<apiKey>');

// define("PROXY", '<proxyURL>:<port>'); 		// just in case the configuration has a proxy. 
?>
