<?php
namespace StalxedTest\FileSystem;

use Stalxed\FileSystem\DirectoryObject;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\FileInfo;

class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'some_directory'   => array(
                'some1.file' => 'some text one',
                'some2.file' => 'some text two',
                'some3.file' => 'some text three'
            ),
            'empty_directory'  => array(),
            'some.file'        => 'some text',
            'empty.file'       => ''
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testCreateDirectory_OneLevel()
    {
        $directory = new FileInfo(vfsStream::url('root/nonexistent_directory'));
        $directory->createDirectory();

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0755, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreateDirectory_ThreeLevels()
    {
        $directory = new FileInfo(vfsStream::url('root/test1/test2/test3'));
        $directory->createDirectory();

        $this->assertTrue($this->root->hasChild('test1'));
        $this->assertEquals(0755, $this->root->getChild('test1')->getPermissions());
        $this->assertTrue($this->root->getChild('test1')->hasChild('test2'));
        $this->assertTrue($this->root->getChild('test1')->getChild('test2')->hasChild('test3'));
    }

    public function testCreateDirectory_ModeSet777()
    {
        $directory = new FileInfo(vfsStream::url('root/nonexistent_directory'));
        $directory->createDirectory(0777);

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0777, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreateDirectory_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new FileInfo(vfsStream::url('root/nonexistent directory'));
        $directory->createDirectory();
    }

    /**
     * @requires PHP 5.4
     *
     */
    public function testCreateFile_NonexistentFile()
    {
        $file = new FileInfo(vfsStream::url('root/nonexistent.file'));
        $file->createFile();

        $this->assertTrue($this->root->hasChild('nonexistent.file'));
        $this->assertEquals(0644, $this->root->getChild('nonexistent.file')->getPermissions());
    }

    /**
     * @requires PHP 5.4
     *
     */
    public function testCreateFile_ModeSet777()
    {
        $file = new FileInfo(vfsStream::url('root/nonexistent.file'));
        $file->createFile(0777);

        $this->assertTrue($this->root->hasChild('nonexistent.file'));
        $this->assertEquals(0777, $this->root->getChild('nonexistent.file')->getPermissions());
    }

    public function testCreateFile_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $file = new FileInfo(vfsStream::url('root/nonexistent.file'));
        $file->createFile();
    }

    public function testCreateFile_ParentDirectoryNotExist()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $file = new FileInfo(vfsStream::url('root/nonexistent_directory/nonexistent.file'));
        $file->createFile();
    }

    public function testDeleteDirectory_EmptyDirectory()
    {
        $file = new FileInfo(vfsStream::url('root/empty_directory'));
        $file->deleteDirectory();

        $this->assertFalse($this->root->hasChild('empty_directory'));
    }

    public function testDeleteDirectory_NonexistentDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $file = new FileInfo(vfsStream::url('root/nonexistent_directory'));
        $file->deleteDirectory();
    }

    public function testDeleteDirectory_SomeFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new FileInfo(vfsStream::url('root/some.file'));
        $directory->deleteDirectory();
    }

    public function testDeleteDirectory_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new FileInfo(vfsStream::url('root/empty_directory'));
        $directory->deleteDirectory();
    }

    public function testDeleteFile_SomeFile()
    {
        $file = new FileInfo(vfsStream::url('root/some.file'));
        $file->deleteFile();

        $this->assertFalse($this->root->hasChild('some.file'));
    }

    public function testDeleteFile_NonexistentFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\FileNotFoundException');

        $file = new FileInfo(vfsStream::url('root/nonexistent.file'));
        $file->deleteFile();
    }

    public function testDeleteFile_SomeDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new FileInfo(vfsStream::url('root/some_directory'));
        $directory->deleteFile();
    }

    public function testDeleteFile_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new FileInfo(vfsStream::url('root/some.file'));
        $directory->deleteFile();
    }

    public function testOpenDirectory_SomeDirectory()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new DirectoryObject(vfsStream::url('root/some_directory'));
        $this->assertEquals($expected, $directory->openDirectory());
    }

    public function testOpenDirectory_SomeFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new FileInfo(vfsStream::url('root/some.file'));
        $directory->openDirectory();
    }
}
