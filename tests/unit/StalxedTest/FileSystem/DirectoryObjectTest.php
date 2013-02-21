<?php
namespace StalxedTest\FileSystem;

use Stalxed\FileSystem\DirectoryObject;
use StalxedTest\FileSystem\TestHelper\Storage;

class DirectoryObjectTest extends \PHPUnit_Framework_TestCase
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

    public function testIsEmpty_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->createFile('some_directory/some3.file');

        $directory = new DirectoryObject($this->storage->getPath('some_directory/'));

        $this->assertFalse($directory->isEmpty());
    }

    public function testIsEmpty_DirectoryContainsSubdirectoriesAndFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createDirectory('some_directory/sub1/');
        $this->storage->createFile('some_directory/sub1/some1.file');
        $this->storage->createFile('some_directory/sub1/some2.file');
        $this->storage->createFile('some_directory/sub1/some3.file');
        $this->storage->createDirectory('some_directory/sub2/');
        $this->storage->createFile('some_directory/sub2/some1.file');
        $this->storage->createFile('some_directory/sub2/some2.file');
        $this->storage->createFile('some_directory/sub2/some3.file');

        $directory = new DirectoryObject($this->storage->getPath('some_directory/'));

        $this->assertFalse($directory->isEmpty());
    }

    public function testIsEmpty_EmptyDirectory()
    {
        $this->storage->createDirectory('empty_directory/');

        $directory = new DirectoryObject($this->storage->getPath('empty_directory/'));

        $this->assertTrue($directory->isEmpty());
    }

    public function testGetSize_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('directory/');
        $this->storage->createDirectory('directory/sub1/');
        $this->storage->createFile('directory/sub1/some1.file');
        $this->storage->createFile('directory/sub1/some2.file');
        $this->storage->createFile('directory/sub1/some3.file');
        $this->storage->createDirectory('directory/sub2/');
        $this->storage->createFile('directory/sub2/some1.file');
        $this->storage->createFile('directory/sub2/some2.file');
        $this->storage->createFile('directory/sub2/some3.file');
        $this->storage->filePutContents('directory/sub1/some1.file', 'some text one');   // 13 bits
        $this->storage->filePutContents('directory/sub1/some2.file', 'some text two');   // 13 bits
        $this->storage->filePutContents('directory/sub1/some3.file', 'some text three'); // 15 bits
        $this->storage->filePutContents('directory/sub2/some1.file', 'some text one');   // 13 bits
        $this->storage->filePutContents('directory/sub2/some2.file', 'some text two');   // 13 bits
        $this->storage->filePutContents('directory/sub2/some3.file', 'some text three'); // 15 bits

        $directory = new DirectoryObject($this->storage->getPath('directory/'));

        $this->assertEquals(82, $directory->getSize());
    }

    public function testGetSize_DirectoryContainsSubdirectoriesAndFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->filePutContents('some_directory/some1.file', 'some text one');   // 13 bits
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->filePutContents('some_directory/some2.file', 'some text two');   // 13 bits
        $this->storage->createFile('some_directory/some3.file');
        $this->storage->filePutContents('some_directory/some3.file', 'some text three'); // 15 bits

        $directory = new DirectoryObject($this->storage->getPath('some_directory/'));

        $this->assertEquals(41, $directory->getSize());
    }

    public function testGetSize_EmptyDirectory()
    {
        $this->storage->createDirectory('empty_directory/');

        $directory = new DirectoryObject($this->storage->getPath('empty_directory/'));

        $this->assertEquals(0, $directory->getSize());
    }

    public function testClear_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->createFile('some_directory/some3.file');

        $directory = new DirectoryObject($this->storage->getPath('some_directory/'));
        $directory->clear();

        $this->assertTrue($directory->isEmpty());
    }

    public function testClear_DirectoryContainsSubdirectoriesAndFiles()
    {
        $this->storage->createDirectory('directory/');
        $this->storage->createDirectory('directory/sub1/');
        $this->storage->createFile('directory/sub1/some1.file');
        $this->storage->createFile('directory/sub1/some2.file');
        $this->storage->createFile('directory/sub1/some3.file');
        $this->storage->createDirectory('directory/sub2/');
        $this->storage->createFile('directory/sub2/some1.file');
        $this->storage->createFile('directory/sub2/some2.file');
        $this->storage->createFile('directory/sub2/some3.file');

        $directory = new DirectoryObject($this->storage->getPath('directory/'));
        $directory->clear();

        $this->assertTrue($directory->isEmpty());
    }

    public function testClear_EmptyDirectory()
    {
        $this->storage->createDirectory('empty_directory/');

        $directory = new DirectoryObject($this->storage->getPath('empty_directory/'));
        $directory->clear();

        $this->assertTrue($directory->isEmpty());
    }

    public function testClear_DirectoryReadOnly()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('directory/');
        $this->storage->createDirectory('directory/sub/');
        $this->storage->createFile('directory/sub/some1.file');
        $this->storage->createFile('directory/sub/some2.file');
        $this->storage->createFile('directory/sub/some3.file');
        $this->storage->chmod('directory/', 0555);

        try {
            $directory = new DirectoryObject($this->storage->getPath('directory/'));
            $directory->clear();
        } catch (\Stalxed\FileSystem\Exception\PermissionDeniedException $e) {
            $this->storage->assertDirectoryExists('directory/sub/');
            $this->storage->assertFileNotExists('directory/sub/some1.file');
            $this->storage->assertFileNotExists('directory/sub/some2.file');
            $this->storage->assertFileNotExists('directory/sub/some3.file');

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testClear_SubDirectoryReadOnly()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('directory/');
        $this->storage->createDirectory('directory/sub/');
        $this->storage->createFile('directory/sub/some1.file');
        $this->storage->createFile('directory/sub/some2.file');
        $this->storage->createFile('directory/sub/some3.file');
        $this->storage->chmod('directory/sub', 0555);

        try {
            $directory = new DirectoryObject($this->storage->getPath('directory/'));
            $directory->clear();
        } catch (\Stalxed\FileSystem\Exception\PermissionDeniedException $e) {
            $this->storage->assertDirectoryExists('directory/sub/');
            $this->storage->assertFileExists('directory/sub/some1.file');
            $this->storage->assertFileExists('directory/sub/some2.file');
            $this->storage->assertFileExists('directory/sub/some3.file');

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCreateDirectoryIterator_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('some_directory/');

        $directory = new DirectoryObject($this->storage->getPath('some_directory/'));

        $expected = new \DirectoryIterator($this->storage->getPath('some_directory/'));
        $expected->setFileClass('Stalxed\FileSystem\FileObject');
        $expected->setInfoClass('Stalxed\FileSystem\FileInfo');
        $this->assertEquals($expected, $directory->createDirectoryIterator());
    }
}
