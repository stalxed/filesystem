<?php
namespace StalxedTest\FileSystem\Control;

use Stalxed\FileSystem\Control\Directory;
use Stalxed\FileSystem\FileInfo;
use StalxedTest\FileSystem\TestHelper\Storage;

class DirectoryTest extends \PHPUnit_Framework_TestCase
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

    public function testCreate_NonexistentDirectory()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $directory = new Directory(new FileInfo($this->storage->getPath('nonexistent_directory/')));
        $directory->create();

        $this->storage->assertDirectoryExists('nonexistent_directory/');
        $this->storage->assertPermissions('755', 'nonexistent_directory/');
    }

    public function testCreate_ThreeLevels()
    {
        $directory = new Directory(new FileInfo($this->storage->getPath('test1/test2/test3')));
        $directory->create();

        $this->storage->assertDirectoryExists('test1/');
        $this->storage->assertDirectoryExists('test1/test2/');
        $this->storage->assertDirectoryExists('test1/test2/test3/');
    }

    public function testCreate_ModeSet777()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $directory = new Directory(new FileInfo($this->storage->getPath('nonexistent_directory/')));
        $directory->create(0777);

        $this->storage->assertDirectoryExists('nonexistent_directory/');
        $this->storage->assertPermissions('777', 'nonexistent_directory/');
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\UnexpectedValueException
     */
    public function testCreate_DirectoryAlreadyExists()
    {
        $this->storage->createDirectory('some_directory/');

        $directory = new Directory(new FileInfo($this->storage->getPath('some_directory/')));
        $directory->create();
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\UnexpectedValueException
     */
    public function testCreate_FileAlreadyExists()
    {
        $this->storage->createFile('some.file');

        $directory = new Directory(new FileInfo($this->storage->getPath('some.file')));
        $directory->create();
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

        $directory = new Directory(new FileInfo($this->storage->getPath('parent_directory/directory/')));
        $directory->create();
    }

    public function testDelete_EmptyDirectory()
    {
        $this->storage->createDirectory('empty_directory/');

        $directory = new Directory(new FileInfo($this->storage->getPath('empty_directory/')));
        $directory->delete();

        $this->storage->assertDirectoryNotExists('empty_directory/');
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\DirectoryNotFoundException
     */
    public function testDelete_NonexistentDirectory()
    {
        $directory = new Directory(new FileInfo($this->storage->getPath('nonexistent_directory/')));
        $directory->delete();
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\DirectoryNotFoundException
     */
    public function testDelete_FileInsteadDirectory()
    {
        $this->storage->createFile('some.file');

        $directory = new Directory(new FileInfo($this->storage->getPath('some.file')));
        $directory->delete();
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\DirectoryNotEmptyException
     */
    public function testDelete_DirectoryContainingFiles()
    {
        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->createFile('some_directory/some3.file');

        $directory = new Directory(new FileInfo($this->storage->getPath('some_directory/')));
        $directory->delete();
    }

    /**
     * @expectedException Stalxed\FileSystem\Exception\PermissionDeniedException
     */
    public function testDelete_ParentDirectoryReadOnly()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('parent_directory/');
        $this->storage->createDirectory('parent_directory/empty_directory/');
        $this->storage->chmod('parent_directory/', 0555);


        $directory = new Directory(new FileInfo($this->storage->getPath('parent_directory/empty_directory/')));
        $directory->delete();
    }

    public function testChmod_DirectoryContainingFiles()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('some_directory/');
        $this->storage->createFile('some_directory/some1.file');
        $this->storage->createFile('some_directory/some2.file');
        $this->storage->createFile('some_directory/some3.file');
        $this->storage->chmod('some_directory/some1.file', 0644);
        $this->storage->chmod('some_directory/some2.file', 0644);
        $this->storage->chmod('some_directory/some3.file', 0644);
        $this->storage->chmod('some_directory/', 0555);

        $directory = new Directory(new FileInfo($this->storage->getPath('some_directory/')));
        $directory->chmod(0777);

        $this->storage->assertPermissions('777', 'some_directory/');
        $this->storage->assertPermissions('644', 'some_directory/some1.file');
        $this->storage->assertPermissions('644', 'some_directory/some2.file');
        $this->storage->assertPermissions('644', 'some_directory/some3.file');
    }

    public function testChmod_EmptyDirectory()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }

        $this->storage->createDirectory('empty_directory/');
        $this->storage->chmod('empty_directory/', 0555);

        $directory = new Directory(new FileInfo($this->storage->getPath('empty_directory/')));
        $directory->chmod(0777);

        $this->storage->assertPermissions('777', 'empty_directory/');
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\DirectoryNotFoundException
     */
    public function testChmod_NonexistentDirectory()
    {
        $directory = new Directory(new FileInfo($this->storage->getPath('nonexistent_directory/')));
        $directory->chmod(0777);
    }

    /**
     * @expectedException Stalxed\FileSystem\Control\Exception\DirectoryNotFoundException
     */
    public function testChmod_FileInsteadDirectory()
    {
        $this->storage->createFile('some.file');

        $directory = new Directory(new FileInfo($this->storage->getPath('some.file')));
        $directory->chmod(0777);
    }
}
