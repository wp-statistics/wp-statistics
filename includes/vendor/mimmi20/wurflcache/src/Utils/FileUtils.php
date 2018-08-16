<?php
/**
 * Copyright (c) 2013-2014 Thomas Müller
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   WurflCache
 *
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */

namespace WurflCache\Utils;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * WURFL File Utilities
 *
 * @category   WurflCache
 *
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 * @copyright  2013-2014 Thomas Müller
 * @license    http://www.opensource.org/licenses/MIT MIT License
 *
 * @link       https://github.com/mimmi20/WurflCache/
 */
class FileUtils
{
    /**
     * Create a directory structure recursiveley
     *
     * @param string $path
     * @param int    $mode
     *
     * @return bool
     */
    public static function mkdir($path, $mode = 0755)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->mkdir($path, $mode);
        } catch (IOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Recursiely remove all files from the given directory NOT including the
     * specified directory itself
     *
     * @param string $path Directory to be cleaned out
     *
     * @return bool
     */
    public static function rmdir($path)
    {
        $files = array_diff(scandir($path), array('.', '..'));

        $filesystem = new Filesystem();

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($file)) {
                continue;
            }

            try {
                $filesystem->remove($file);
            } catch (IOException $exception) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the unserialized contents of the given $file
     *
     * @param string $file filename
     *
     * @return mixed Unserialized data or null if file does not exist
     */
    public static function read($file)
    {
        if (!is_readable($file) || !is_file($file)) {
            return;
        }

        $data = file_get_contents($file);

        if ($data === false) {
            return;
        }

        return $data;
    }

    /**
     * Serializes and saves $data in the file $path and sets the last modified time to $mtime
     *
     * @param string $filename filename to save data in
     * @param mixed  $data     data to be serialized and saved
     * @param int    $mtime    Last modified date in epoch time
     *
     * @return bool
     */
    public static function write($filename, $data, $mtime = 0)
    {
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            self::mkdir($dir, 0755);
        } elseif (!is_writable($dir)) {
            return false;
        }

        $stream         = self::detectStream($filename);
        $limitedStreams = array('vfs');

        if (!in_array($stream, $limitedStreams)) {
            $mode = 0755;
        } else {
            // does not work with vfs stream
            $mode = null;
        }

        $filesystem = new Filesystem();
        $tmpFile    = $dir . '/temp_' . md5(basename($filename));

        if (false === file_put_contents($tmpFile, $data)) {
            return false;
        }

        try {
            $filesystem->rename($tmpFile, $filename, true);

            if (null !== $mode) {
                $filesystem->chmod($filename, $mode);
            }
        } catch (IOException $exception) {
            return false;
        }

        if (!file_exists($filename)) {
            return false;
        }

        if (!in_array($stream, $limitedStreams) || version_compare(PHP_VERSION, '5.4.0', '>=')) {
            // does not work with vfs stream on PHP 5.3
            $mtime = ($mtime > 0) ? $mtime : time();

            try {
                $filesystem->touch($filename, $mtime);
            } catch (IOException $exception) {
                return false;
            }
        }

        return true;
    }

    /**
     * Combines given array of $strings into a proper filesystem path
     *
     * @param array $strings Array of (string)path members
     *
     * @return string Proper filesystem path
     */
    public static function join(array $strings = array())
    {
        return implode('/', $strings);
    }

    /**
     * detects if the the path is linked to an file stream
     *
     * @param $path
     *
     * @return string
     */
    private static function detectStream($path)
    {
        $stream = 'file';

        if (false !== strpos($path, '://')) {
            $parts  = explode('://', $path);
            $stream = $parts[0];
        }

        return $stream;
    }

    /**
     * Returns TRUE, if the file exists, FALSE otherwise
     *
     * @param string $file filename
     *
     * @return bool
     */
    public static function exists($file)
    {
        $filesystem = new Filesystem();

        return $filesystem->exists($file);
    }
}
