<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Client\FtpClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * A File handling class
 *
 * @since  1.7.0
 */
class File
{
	/**
	 * Gets the extension of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file extension
	 *
	 * @since   1.7.0
	 */
	public static function getExt($file)
	{
		$dot = strrpos($file, '.');

		if ($dot === false)
		{
			return '';
		}

		return (string) substr($file, $dot + 1);
	}

	/**
	 * Strips the last extension off of a file name
	 *
	 * @param   string  $file  The file name
	 *
	 * @return  string  The file name without the extension
	 *
	 * @since   1.7.0
	 */
	public static function stripExt($file)
	{
		return preg_replace('#\.[^.]*$#', '', $file);
	}

	/**
	 * Makes file name safe to use
	 *
	 * @param   string  $file  The name of the file [not full path]
	 *
	 * @return  string  The sanitised string
	 *
	 * @since   1.7.0
	 */
	public static function makeSafe($file)
	{
		// Remove any trailing dots, as those aren't ever valid file names.
		$file = rtrim($file, '.');

		$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

		return trim(preg_replace($regex, '', $file));
	}

	/**
	 * Copies a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function copy($src, $dest, $path = null, $use_streams = false)
	{
		// Prepend a base path if it exists
		if ($path)
		{
			$src = Path::clean($path . '/' . $src);
			$dest = Path::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_FIND_COPY', $src), Log::WARNING, 'jerror');

			return false;
		}

		if ($use_streams)
		{
			$stream = Factory::getStream();

			if (!$stream->copy($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_STREAMS', $src, $dest, $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// If the parent folder doesn't exist we must create it
				if (!file_exists(\dirname($dest)))
				{
					Folder::create(\dirname($dest));
				}

				// Translate the destination path for the FTP account
				$dest = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				if (!$ftp->store($src, $dest))
				{
					// FTP connector throws an error
					return false;
				}

				$ret = true;
			}
			else
			{
				if (!@ copy($src, $dest))
				{
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_COPY_FAILED_ERR01', $src, $dest), Log::WARNING, 'jerror');

					return false;
				}

				$ret = true;
			}

			return $ret;
		}
	}

	/**
	 * Delete a file or array of files
	 *
	 * @param   mixed  $file  The file name or an array of file names
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function delete($file)
	{
		$FTPOptions = ClientHelper::getCredentials('ftp');

		if (\is_array($file))
		{
			$files = $file;
		}
		else
		{
			$files[] = $file;
		}

		// Do NOT use ftp if it is not enabled
		if ($FTPOptions['enabled'] == 1)
		{
			// Connect the FTP client
			$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
		}

		foreach ($files as $file)
		{
			$file = Path::clean($file);

			if (!is_file($file))
			{
				continue;
			}

			// Try making the file writable first. If it's read-only, it can't be deleted
			// on Windows, even if the parent folder is writable
			@chmod($file, 0777);

			// In case of restricted permissions we zap it one way or the other
			// as long as the owner is either the webserver or the ftp
			if (@unlink($file))
			{
				// Do nothing
			}
			elseif ($FTPOptions['enabled'] == 1)
			{
				$file = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');

				if (!$ftp->delete($file))
				{
					// FTP connector throws an error

					return false;
				}
			}
			else
			{
				$filename = basename($file);
				Log::add(Text::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), Log::WARNING, 'jerror');

				return false;
			}
		}

		return true;
	}

	/**
	 * Moves a file
	 *
	 * @param   string   $src          The path to the source file
	 * @param   string   $dest         The path to the destination file
	 * @param   string   $path         An optional base path to prefix to the file names
	 * @param   boolean  $use_streams  True to use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function move($src, $dest, $path = '', $use_streams = false)
	{
		if ($path)
		{
			$src = Path::clean($path . '/' . $src);
			$dest = Path::clean($path . '/' . $dest);
		}

		// Check src path
		if (!is_readable($src))
		{
			Log::add(Text::_('JLIB_FILESYSTEM_CANNOT_FIND_SOURCE_FILE'), Log::WARNING, 'jerror');

			return false;
		}

		if ($use_streams)
		{
			$stream = Factory::getStream();

			if (!$stream->move($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_JFILE_MOVE_STREAMS', $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account
				$src = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $src), '/');
				$dest = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Use FTP rename to simulate move
				if (!$ftp->rename($src, $dest))
				{
					Log::add(Text::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), Log::WARNING, 'jerror');

					return false;
				}
			}
			else
			{
				if (!@ rename($src, $dest))
				{
					Log::add(Text::_('JLIB_FILESYSTEM_ERROR_RENAME_FILE'), Log::WARNING, 'jerror');

					return false;
				}
			}

			return true;
		}
	}

	/**
	 * Write contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   $buffer       The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function write($file, $buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(\dirname($file)))
		{
			if (Folder::create(\dirname($file)) == false)
			{
				return false;
			}
		}

		if ($use_streams)
		{
			$stream = Factory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if (!$stream->writeFile($file, $buffer))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->write($file, $buffer);
			}
			else
			{
				$file = Path::clean($file);
				$ret = \is_int(file_put_contents($file, $buffer)) ? true : false;
			}

			return $ret;
		}
	}

	/**
	 * Append contents to a file
	 *
	 * @param   string   $file         The full file path
	 * @param   string   $buffer       The buffer to write
	 * @param   boolean  $use_streams  Use streams
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public static function append($file, $buffer, $use_streams = false)
	{
		@set_time_limit(ini_get('max_execution_time'));

		// If the file doesn't exist, just write instead of append
		if (!file_exists($file))
		{
			return self::write($file, $buffer, $use_streams);
		}

		if ($use_streams)
		{
			$stream = Factory::getStream();

			// Beef up the chunk size to a meg
			$stream->set('chunksize', (1024 * 1024));

			if ($stream->open($file, 'ab') && $stream->write($buffer) && $stream->close())
			{
				return true;
			}

			Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', $file, $stream->getError()), Log::WARNING, 'jerror');

			return false;
		}
		else
		{
			// Initialise variables.
			$FTPOptions = ClientHelper::getCredentials('ftp');

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account and use FTP write buffer to file
				$file = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
				$ret = $ftp->append($file, $buffer);
			}
			else
			{
				$file = Path::clean($file);
				$ret = \is_int(file_put_contents($file, $buffer, FILE_APPEND));
			}

			return $ret;
		}
	}

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string   $src              The name of the php (temporary) uploaded file
	 * @param   string   $dest             The path (including filename) to move the uploaded file to
	 * @param   boolean  $use_streams      True to use streams
	 * @param   boolean  $allow_unsafe     Allow the upload of unsafe files
	 * @param   boolean  $safeFileOptions  Options to InputFilter::isSafeFile
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public static function upload($src, $dest, $use_streams = false, $allow_unsafe = false, $safeFileOptions = array())
	{
		if (!$allow_unsafe)
		{
			$descriptor = array(
				'tmp_name' => $src,
				'name'     => basename($dest),
				'type'     => '',
				'error'    => '',
				'size'     => '',
			);

			$isSafe = InputFilter::isSafeFile($descriptor, $safeFileOptions);

			if (!$isSafe)
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR03', $dest), Log::WARNING, 'jerror');

				return false;
			}
		}

		// Ensure that the path is valid and clean
		$dest = Path::clean($dest);

		// Create the destination directory if it does not exist
		$baseDir = \dirname($dest);

		if (!file_exists($baseDir))
		{
			Folder::create($baseDir);
		}

		if ($use_streams)
		{
			$stream = Factory::getStream();

			if (!$stream->upload($src, $dest))
			{
				Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_UPLOAD', $stream->getError()), Log::WARNING, 'jerror');

				return false;
			}

			return true;
		}
		else
		{
			$FTPOptions = ClientHelper::getCredentials('ftp');
			$ret = false;

			if ($FTPOptions['enabled'] == 1)
			{
				// Connect the FTP client
				$ftp = FtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);

				// Translate path for the FTP account
				$dest = Path::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $dest), '/');

				// Copy the file to the destination directory
				if (is_uploaded_file($src) && $ftp->store($src, $dest))
				{
					unlink($src);
					$ret = true;
				}
				else
				{
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), Log::WARNING, 'jerror');
				}
			}
			else
			{
				if (is_writable($baseDir) && move_uploaded_file($src, $dest))
				{
					// Short circuit to prevent file permission errors
					if (Path::setPermissions($dest))
					{
						$ret = true;
					}
					else
					{
						Log::add(Text::_('JLIB_FILESYSTEM_ERROR_WARNFS_ERR01'), Log::WARNING, 'jerror');
					}
				}
				else
				{
					Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR04', $src, $dest), Log::WARNING, 'jerror');
				}
			}

			return $ret;
		}
	}

	/**
	 * Wrapper for the standard file_exists function
	 *
	 * @param   string  $file  File path
	 *
	 * @return  boolean  True if path is a file
	 *
	 * @since   1.7.0
	 */
	public static function exists($file)
	{
		return is_file(Path::clean($file));
	}
}
