<?php
namespace StalxedTest\FileSystem\Control;

use Stalxed\FileSystem\Control\File;
use Stalxed\FileSystem\FileInfo;
use StalxedTest\FileSystem\TestHelper\Storage;

class FileTest extends \PHPUnit_Framework_TestCase
{
    private $root;
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

    public function testCreate_NonexistentFile()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $file = new File(new FileInfo($this->storage->getPath('nonexistent.file')));
        $file->create();

        $this->storage->assertFileExists('nonexistent.file');
        $this->storage->assertPermissions('644', 'nonexistent.file');
    }

    public function testCreate_ModeSet777()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $file = new File(new FileInfo($this->storage->getPath('nonexistent.file')));
        $file->create(0777);

        $this->storage->assertFileExists('nonexistent.file');
        $this->storage->assertPermissions('777', 'nonexistent.file');
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\DirectoryNotFoundException
     */
    public function testCreate_ParentDirectoryNotExist()
    {
        $file = new File(new FileInfo($this->storage->getPath('nonexistent_directory/nonexistent.file')));
        $file->create();
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\UnexpectedValueException
     */
    public function testCreate_FileAlreadyExists()
    {
        $this->storage->createFile('some.file');

        $file = new File(new FileInfo($this->storage->getPath('some.file')));
        $file->create(0777);
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\UnexpectedValueException
     */
    public function testCreate_DirectoryAlreadyExists()
    {
        $this->storage->createDirectory('some_directory');

        $file = new File(new FileInfo($this->storage->getPath('some_directory')));
        $file->create(0777);
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\PermissionDeniedException
     */
    public function testCreate_ParentDirectoryReadOnly()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('parent_directory/');
        $this->storage->chmod('parent_directory/', 0555);

        $file = new File(new FileInfo($this->storage->getPath('parent_directory/some.file')));
        $file->create(0777);
    }

    public function testDelete_FileContainingText()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new File(new FileInfo($this->storage->getPath('some.file')));
        $file->delete();

        $this->assertFileNotExists('some.file');
    }

    public function testDelete_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new File(new FileInfo($this->storage->getPath('empty.file')));
        $file->delete();

        $this->assertFileNotExists('empty.file');
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\FileNotFoundException
     */
    public function testDelete_NonexistentFile()
    {
        $file = new File(new FileInfo($this->storage->getPath('nonexistent.file')));
        $file->delete();
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\FileNotFoundException
     */
    public function testDelete_DirectoryInsteadFile()
    {
        $this->storage->createDirectory('some_directory');

        $file = new File(new FileInfo($this->storage->getPath('some_directory')));
        $file->delete();
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\PermissionDeniedException
     */
    public function testDelete_ParentDirectoryReadOnly()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('parent_directory/');
        $this->storage->createFile('parent_directory/some.file');
        $this->storage->chmod('parent_directory/', 0555);


        $file = new File(new FileInfo($this->storage->getPath('parent_directory/some.file')));
        $file->delete();
    }

    public function testChmod_FileContainingText()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');
        $this->storage->chmod('some.file', 0555);

        $file = new File(new FileInfo($this->storage->getPath('some.file')));
        $file->chmod(0777);

        $this->storage->assertPermissions('777', 'some.file');
    }

    public function testChmod_EmptyFile()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createFile('empty.file');
        $this->storage->chmod('empty.file', 0555);

        $file = new File(new FileInfo($this->storage->getPath('empty.file')));
        $file->chmod(0777);

        $this->storage->assertPermissions('777', 'empty.file');
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\FileNotFoundException
     */
    public function testChmod_NonexistentFile()
    {
        $file = new File(new FileInfo($this->storage->getPath('nonexistent.file')));
        $file->chmod(0777);
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\FileNotFoundException
     */
    public function testChmod_DirectoryInsteadFile()
    {
        $this->storage->createDirectory('some_directory');

        $file = new File(new FileInfo($this->storage->getPath('some_directory')));
        $file->chmod(0777);
    }
}
