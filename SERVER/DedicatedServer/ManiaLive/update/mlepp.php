<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 * 
 * -- MLEPP Package --
 * @name Updater
 * @date 12-06-2011
 * @version r812
 * @website mlepp.trackmania.nl
 * @package MLEPP
 * 
 * @author ManiaLive developers
 * @copyright 2011
 * 
 * @reauthored Max "TheM" Klaversma <maxklaversma@gmail.com>
 * @copyright 2010 - 2011
 * 
 * Used the ManiaLive updater and updated it for MLEPP.
 * Thanks to the ManiaLive developers !
 * 
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things of use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */

const NL = "\n";

echo '###############################################################################' . NL;
echo '#                  ManiaLive Extending Plugin Pack - Updater                  #' . NL;
echo '###############################################################################' . NL;
echo '#                                Terms of use                                 #' . NL;
echo '#-----------------------------------------------------------------------------#' . NL;
echo '# You are allowed to use this updater to update your MLEPP installation to    #' . NL;
echo '# the latest version.                                                         #' . NL;
echo '# REMEMBER: we aren\'t responsibly for compatibility problems etc.             #' . NL;
echo '#-----------------------------------------------------------------------------#' . NL;
echo '#                               Use at own risk.                              #' . NL;
echo '###############################################################################' . NL;

echo 'Do accept the terms of use? (y/n):';
	
$in = strtolower(trim(fgets(STDIN)));
if ($in != 'y')
{
	die ('> Not accepted the terms of use, quit!' . NL);
}

echo '> Checking local MLEPP version ...' . NL;

// the noupdate file will prevent the updater from working its magic.
if (file_exists('../libraries/ManiaLivePlugins/MLEPP/noupdate')) 
{
	die('ERROR: This version is locked for updates!' . NL);
}

require_once '../utils.inc.php';

$includes = array(
	'../libraries/ManiaLivePlugins/MLEPP/Core/Core.php'
);

// start loading dependencies
$success = true;
foreach ($includes as $inc)
{
	if (file_exists($inc))
	{
		include_once($inc);
	}
	else
	{
		$success = false;
	}
}

// if everything's there, then get the version number
if ($success)
{
    $mleppVersion = \ManiaLivePlugins\MLEPP\Core\Core::$version;
	$versionLocal = $mleppVersion;
}
else // otherwise something broke and we need to do an update!
{
	$versionLocal = 0;
}

echo '> MLEPP is at version ' . $versionLocal . NL;

echo '> Checking remote MLEPP version ...' . NL;

// check for manialive update
$versionRemote = 0;
$versionDownloadUrl = '';
$versionUpdate = false;

echo 'To which version do you want to upgrade? (stable/dev):';
$in = strtolower(trim(fgets(STDIN)));
if ($in == 'dev')
{
	$whichUpdate = 'dev';
}
else
{
    $whichUpdate = 'stable';
}
	
echo NL;

try
{
    if($whichUpdate == 'stable')
    {
        $versionRemote = file_get_contents('http://mlepp.klaversma.eu/mlepp_version.txt');
        if($mleppVersion >= $versionRemote)
        {
            $versionUpdate = false;
        }
        else
        {
            $versionUpdate = true;
        }
        $versionDownloadUrl = 'http://mlepp.googlecode.com/files/mlepp_plugins_all_r'.$versionRemote.'.zip';
        $versionDownloadBasename = 'mlepp_plugins_all_r'.$versionRemote.'.zip';
    }
    elseif($whichUpdate == 'dev')
    {
        $versionRemote = file_get_contents('http://mlepp.klaversma.eu/mlepp_version_dev.txt');
        if($mleppVersion >= $versionRemote)
        {
            $versionUpdate = false;
        }
        else
        {
            $versionUpdate = true;
        }
        $versionDownloadUrl = 'http://mlepp.klaversma.eu/files/mlepp_dev_r'.$versionRemote.'.zip';
        $versionDownloadBasename = 'mlepp_dev_r'.$versionRemote.'.zip';
    }
}
catch (\Exception $ex)
{
	die('ERROR: It is currently not possible to access MLEPP webservice!' . NL);
}

echo '> Remote MLEPP is at version ' . $versionRemote . NL;

// no need to update when there's already the latest version installed
if (!$versionUpdate)
{
	echo 'Local version is the same or even newer than the remote one.' . NL;
	echo 'Do you want to proceed? (y/n):';
	
	$in = strtolower(trim(fgets(STDIN)));
	if ($in != 'y')
	{
		die ('> Local version is already uptodate, taking no action!' . NL);
	}
	
	echo NL;
}

// create temporary folder
if (!is_dir('./temp'))
{
	echo "> Creating temporary directory ..." .NL;
	mkdir('./temp');
}

if (!is_dir('./temp/mlepp'))
{
	echo "> Creating temporary MLEPP directory ..." .NL;
	mkdir('./temp/mlepp');
}

// get file name
$info = pathinfo($versionDownloadUrl);
$package = $versionDownloadBasename;

echo "> Downloading '" . $package . "' ..." .NL;

// download and save the package
$data = @file_get_contents($versionDownloadUrl);

// check for errors
if ($data === false)
{
	die('ERROR: The file could not be retrieved from the server!' . NL);
}

echo "> OK." . NL;

file_put_contents('./temp/' . $package, $data);

echo NL;

echo 'Everything is in place.' . NL;
echo '[local: ' . $versionLocal . '] ---> [remote: ' . $versionRemote . ']' . NL;
echo 'Do you want to update MLEPP now? (y/n):';

// parsing user input
$in = strtolower(trim(fgets(STDIN)));
if ($in != 'y')
{
	echo NL;
	
	echo '> Cleaning up!' . NL;
	
	rrmdir('./temp');
	
	die('Aborted by user!' . NL);
}

echo '> Extract files ...' . NL;

// check if zip library is loaded ...
if (!class_exists('ZipArchive'))
{
	die('class ZipArchive does not exist, you need to'.
		'enable the zip extension for your php version!' . NL);
}

// try to extract the archive
$zip = new ZipArchive;
$res = $zip->open('./temp/' . $package);

if ($res !== true)
{
	die('ERROR: Could not extract zip archive!' . NL);
}

$zip->extractTo('./temp/mlepp/');
$zip->close();

echo NL;

echo '> Removing old directories ...' . NL;
rrmdir('../libraries/ManiaLivePlugins/MLEPP');
@unlink('../config/config-mlepp-example.ini');
@unlink('../config/config-mlepp-widgets-example.ini');
@unlink('../update/mlepp.php');

echo NL;

echo '> Copying new files ...' . NL;
rcopy('./temp/mlepp/libraries/ManiaLivePlugins/MLEPP', '../libraries/ManiaLivePlugins/MLEPP');
rcopy('./temp/mlepp/config', '../config');
copy('./temp/mlepp/update/mlepp.php', '../update/mlepp.php'); // update the updater itself!

echo NL;

echo '> Cleaning up ...' . NL;

rrmdir('./temp');

echo '>> Done!' . NL;
echo '###############################################################################' . NL;
echo '>> Please check your configuration files !' . NL;
echo '>> The ...-example.ini files are the MLEPP examples delivered with the latest ' . NL;
echo '>> release, change your own configuration files if needed !' . NL;
echo '###############################################################################' . NL;

/**
 * Recursively remove a directory.
 * @param $dir Directory to remove.
 */
function rrmdir($dir)
{
	if (!is_dir($dir)) return;

	if (substr($dir, strlen($dir)-1, 1) != '/')
	$dir .= '/';

	echo 'deleting: ' . $dir . NL;

	if ($handle = opendir($dir))
	{
		while ($obj = readdir($handle))
		{
			if ($obj != '.' && $obj != '..')
			{
				if (is_dir($dir.$obj))
				{
					rrmdir($dir.$obj);
				}
				elseif (is_file($dir.$obj))
				{
					unlink($dir.$obj);
				}
			}
		}
		closedir($handle);
		return rmdir($dir);
	}
	return false;
}

/**
 * Recursively copy a directory.
 * @param string $src Source directory.
 * @param string $dst Destination directory.
 */
function rcopy($src, $dst)
{
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ($file = readdir($dir)))
	{
		if (($file != '.') && ($file != '..'))
		{
			if (is_dir($src . '/' . $file))
			{
				rcopy($src . '/' . $file, $dst . '/' . $file);
			}
			else
			{
				echo 'copying: ' . $src . NL;
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}
?>
