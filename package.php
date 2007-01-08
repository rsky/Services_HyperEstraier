<?php
require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
//date_default_timezone_set('UTC');

// general package information
$packagename  = 'Services_HyperEstraier';
$summary      = 'PHP interface of Hyper Estraier';

$description  = 'A porting of estraierpure.rb which is a part of Hyper Estraier.

Hyper Estraier is a full-text search system. You can search lots of
documents for some documents including specified words. If you run a web
site, it is useful as your own search engine for pages in your site.
Also, it is useful as search utilities of mail boxes and file servers.';

// information of cureent version
$version      = '0.6.0';
$apiversion   = '1.4.9';
$stability    = 'beta';
$apistability = 'stable';

//$notes = '* Changed channel __uri to pear.paradogs.jp (pdx).';
$notes = '* Relicensed under MIT license.
* Change channel from pear.php.net (pear) to __uri.
* Catch up with Hyper Estraier 1.4.9.
* Add support for the attribute distinction filter in Condition class.
  (Services_HyperEstraier_Condition::{set,get}Distinct($name))
* Add class Services_HyperEstraier_Error which wraps PEAR_ErrorStack.
* Add support for HTTP stream with cURL wrapper.
* Remove support for PEAR::HTTP_Request.';

// set parameters to the package
$packagexml = new PEAR_PackageFileManager2;
$packagexml->setOptions(array(
    //'packagefile'       => 'package2.xml',
    'baseinstalldir'    => 'Services',
    'packagedirectory'  => dirname(__FILE__),
    'filelistgenerator' => 'file',
    'ignore'    => array(
        '.DS_Store',
        '.svn',
        'package.php',
        'package.xml*',
        'test.sh'),
    'dir_roles' => array(
        'examples'  => 'doc',
        'tests'     => 'test'),
    'exceptions'    => array(
        'COPYING'       => 'doc',
        'ChangeLog'     => 'doc',
        'README'        => 'doc',
        'RELEASE_NOTES' => 'doc',
        'TODO'          => 'doc')));

$packagexml->setPackage($packagename);
$packagexml->setSummary($summary);
$packagexml->setNotes($notes);
$packagexml->setDescription($description);
$packagexml->setLicense('MIT License', 'http://www.opensource.org/licenses/mit-license.php');

$packagexml->setReleaseVersion($version);
$packagexml->setAPIVersion($apiversion);
$packagexml->setReleaseStability($stability);
$packagexml->setAPIStability($apistability);

$packagexml->addMaintainer('lead', 'rsk', 'Ryusuke SEKIYAMA', 'rsky0711@gmail.com');

$packagexml->setPackageType('php');
$packagexml->setPhpDep('5.1.0');
$packagexml->setPearinstallerDep('1.4.0');
$packagexml->addExtensionDep('required', 'pcre');
$packagexml->addExtensionDep('required', 'sockets');
$packagexml->addExtensionDep('required', 'SPL');
$packagexml->addExtensionDep('optional', 'http');
$packagexml->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net');
//$packagexml->addPackageDepWithChannel('optional', 'HTTP_Request', 'pear.php.net');
//$packagexml->addPackageDepWithChannel('optional', 'pecl_http', 'pecl.php.net');
//$packagexml->addConflictingPackageDepWithChannel($packagename, 'pear.php.net');

$packagexml->addGlobalReplacement('package-info', '@package_version@', 'version');

$packagexml->setChannel('__uri');
$packagexml->generateContents();

// get a PEAR_PackageFile object
//$packagexml1 = &$packagexml->exportCompatiblePackageFile1();

// generate package.xml
if (php_sapi_name() == 'cli' && $argc > 1 && $argv[1] == 'make') {
    $make = true;
} elseif (!empty($_GET['make'])) {
    $make = true;
} else {
    $make = false;
}
// note use of debugPackageFile() - this is VERY important
if ($make) {
    //$packagexml1->writePackageFile();
    $packagexml->writePackageFile();
} else {
    //$packagexml1->debugPackageFile();
    $packagexml->debugPackageFile();
}
