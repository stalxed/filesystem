<?php
namespace StalxedTest\FileSystem\TestHelper;

use Stalxed\FileSystem\FileInfo;
use PHPUnit_Framework_TestCase as TestCase;

class Storage extends TestCase
{
	protected function tearDown()
	{
		$fileinfo = new FileInfo($this->getPathStore());
        $fileinfo->openDirectory()->chmodInternalContent(0777);
        $fileinfo->openDirectory()->clear();

        clearstatcache(true);

		parent::tearDown();
	}

    public static function getPath($name)
    {
        return self::getPathStore() . '/' . $name;
    }

    public static function getPathStore()
    {
        return realpath(__DIR__ . '/_files/test_fs_helper/');
    }

    public static function createFile($name)
    {
    	touch(self::getPath($name));
    }

    public static function createDirectory($name)
    {
    	mkdir(self::getPath($name));
    }

    public static function chmod($name, $mode)
    {
    	chmod(self::getPath($name), $mode);
    }

    public static function fileGetContents($filename)
    {
    	return file_get_contents(self::getPath($filename));
    }

    public static function filePutContents($filename, $data)
    {
    	file_put_contents(self::getPath($filename), $data, LOCK_EX);
    }

    public static function assertFileExists($name, $message = '')
    {
    	parent::assertFileExists(self::getPath($name), $message);
    }

    public static function assertFileNotExists($name, $message = '')
    {
        parent::assertFileNotExists(self::getPath($name), $message);
    }

    public static function assertFileExistsAndContains($name, $value, $message = '')
    {
    	self::assertFileExists($name, $message);
    	self::assertSame($value, self::fileGetContents($name), $message);
    }

    public static function assertDirectoryExists($name, $message = '')
	{
		self::assertTrue(is_dir(self::getPath($name)), $message);
	}

    public static function assertDirectoryNotExists($name, $message = '')
	{
		self::assertFalse(is_dir(self::getPath($name)), $message);
	}

	public static function assertPermissions($excpected, $filename)
	{
	    clearstatcache(true);

	    $perms = fileperms(self::getPath($filename));
	    $rigth = substr(decoct($perms), -3);

        self::assertEquals($excpected, $rigth, self::getPath($filename) . ':' . $perms);
	}
}
