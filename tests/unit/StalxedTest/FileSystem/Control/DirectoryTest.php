<?php
namespace StalxedTest\FileSystem\Control;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\Control\Directory;
use Stalxed\FileSystem\FileInfo;

class DirectoryTest extends \PHPUnit_Framework_TestCase
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
            'some.file'        => 'some text'
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testCreate_OneLevel()
    {
        $directory = new Directory(new FileInfo(vfsStream::url('root/nonexistent_directory')));
        $directory->create();

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0755, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreate_ThreeLevels()
    {
        $directory = new Directory(new FileInfo(vfsStream::url('root/test1/test2/test3')));
        $directory->create();

        $this->assertTrue($this->root->hasChild('test1'));
        $this->assertEquals(0755, $this->root->getChild('test1')->getPermissions());
        $this->assertTrue($this->root->getChild('test1')->hasChild('test2'));
        $this->assertTrue($this->root->getChild('test1')->getChild('test2')->hasChild('test3'));
    }

    public function testCreate_ModeSet777()
    {
        $directory = new Directory(new FileInfo(vfsStream::url('root/nonexistent_directory')));
        $directory->create(0777);

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0777, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreate_DirectoryAlreadyExists()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/some_directory')));
        $directory->create();
    }

    public function testCreate_FileAlreadyExists()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/some.file')));
        $directory->create();
    }

    public function testCreate_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new Directory(new FileInfo(vfsStream::url('root/nonexistent_directory')));
        $directory->create();
    }

    public function testDelete_EmptyDirectory()
    {
        $directory = new Directory(new FileInfo(vfsStream::url('root/empty_directory')));
        $directory->delete();

        $this->assertFalse($this->root->hasChild('empty_directory'));
    }

    public function testDelete_NonexistentDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/nonexistent_directory')));
        $directory->delete();
    }

    public function testDelete_FileInsteadDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/some.file')));
        $directory->delete();
    }

    public function testDelete_DirectoryContainingFiles()
    {
        $this->setExpectedException('Stalxed\FileSystem\Control\Exception\DirectoryNotEmptyException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/some_directory/')));
        $directory->delete();
    }

    public function testDelete_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new Directory(new FileInfo(vfsStream::url('root/empty_directory')));
        $directory->delete();
    }

    public function testChmod_DirectoryContainingFiles()
    {
        $someDirectory = $this->root->getChild('some_directory');
        $someDirectory->getChild('some1.file')->chmod(0444);
        $someDirectory->getChild('some2.file')->chmod(0444);
        $someDirectory->getChild('some3.file')->chmod(0444);
        $someDirectory->chmod(0555);

        $directory = new Directory(new FileInfo(vfsStream::url('root/some_directory/')));
        $directory->chmod(0777);

        $this->assertEquals(0777, $someDirectory->getPermissions());
        $this->assertEquals(0444, $someDirectory->getChild('some1.file')->getPermissions());
        $this->assertEquals(0444, $someDirectory->getChild('some2.file')->getPermissions());
        $this->assertEquals(0444, $someDirectory->getChild('some3.file')->getPermissions());
    }

    public function testChmod_EmptyDirectory()
    {
        $this->root->getChild('some_directory')->chmod(0555);

        $directory = new Directory(new FileInfo(vfsStream::url('root/empty_directory/')));
        $directory->chmod(0777);

        $this->assertEquals(0777, $this->root->getChild('empty_directory')->getPermissions());
    }

    public function testChmod_NonexistentDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/nonexistent_directory')));
        $directory->chmod(0777);
    }

    public function testChmod_FileInsteadDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $directory = new Directory(new FileInfo(vfsStream::url('root/some.file')));
        $directory->chmod(0777);
    }

    public function testChmod_ParentDirectoryReadOnly()
    {
        $this->markTestSkipped('Limitation of the current version of the file system.');
    }
}
