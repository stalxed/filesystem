<?php
namespace StalxedTest\FileSystem;

use Stalxed\FileSystem\Control;
use Stalxed\FileSystem\DirectoryObject;
use Stalxed\FileSystem\FileInfo;
use StalxedTest\FileSystem\TestHelper\Storage;

class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    private $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = new Storage();
    }

    protected function tearDown()
    {
        $this->storage->tearDown();

        parent::tearDown();
    }

    public function testGetSize_SomeDirectory()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->filePutContents('some_directory/some1.file', 'some text one');   // 13 bits
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->filePutContents('some_directory/some2.file', 'some text two');   // 13 bits
        $this->storage->createFile('some_directory/some3.file');
        $this->storage->filePutContents('some_directory/some3.file', 'some text three'); // 15 bits

        $fileinfo = new FileInfo($this->storage->getPath('some_directory/'));

        $this->assertEquals(41, $fileinfo->getSize());
    }

    public function testGetSize_SomeFile()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text one'); // 13 bits

        $fileinfo = new FileInfo($this->storage->getPath('some.file'));

        $this->assertEquals(13, $fileinfo->getSize());
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\PathNotFoundException
     */
    public function testGetSize_UnknownPath()
    {
        $fileinfo = new FileInfo($this->storage->getPath('nonexistent_directory/nonexistent.file'));
        $fileinfo->getSize();
    }

    public function testIsEmpty_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->createFile('some_directory/some3.file');

        $fileinfo = new FileInfo($this->storage->getPath('some_directory/'));

        $this->assertFalse($fileinfo->isEmpty());
    }

    public function testIsEmpty_EmptyDirectory()
    {
        $this->storage->createDirectory('empty_directory/');

        $fileinfo = new FileInfo($this->storage->getPath('empty_directory/'));

        $this->assertTrue($fileinfo->isEmpty());
    }

    public function testIsEmpty_FileContainsText()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $fileinfo = new FileInfo($this->storage->getPath('some.file'));

        $this->assertFalse($fileinfo->isEmpty());
    }

    public function testIsEmpty_EmptyFile()
    {
        $this->storage->createFile('some.file');

        $fileinfo = new FileInfo($this->storage->getPath('some.file'));

        $this->assertTrue($fileinfo->isEmpty());
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\PathNotFoundException
     */
    public function testIsEmpty_UnknownPath()
    {
        $fileinfo = new FileInfo($this->storage->getPath('nonexistent_directory/nonexistent.file'));
        $fileinfo->isEmpty();
    }

    public function testOpenDirectory_SomeDirectory()
    {
        $this->storage->createDirectory('some_directory/');

        $fileinfo = new FileInfo($this->storage->getPath('some_directory/'));

        $expected = new DirectoryObject($fileinfo->getRealPath());
        $this->assertEquals($expected, $fileinfo->openDirectory());
    }

    public function testControl_SomeDirectory()
    {
        $this->storage->createDirectory('some_directory/');

        $fileinfo = new FileInfo($this->storage->getPath('some_directory/'));

        $expected = new Control\Directory($fileinfo);
        $this->assertEquals($expected, $fileinfo->control());
    }

    public function testControl_SomeFile()
    {
        $this->storage->createFile('some.file');

        $fileinfo = new FileInfo($this->storage->getPath('some.file'));

        $expected = new Control\File($fileinfo);
        $this->assertEquals($expected, $fileinfo->control());
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\PathNotFoundException
     */
    public function testControl_UnknownPath()
    {
        $fileinfo = new FileInfo($this->storage->getPath('nonexistent_directory/nonexistent.file'));
        $fileinfo->control();
    }
}
