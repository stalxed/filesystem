<?php
namespace StalxedTest\FileSystem\Control;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\Control\Directory;

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
            'some.file'        => 'some text',
            'empty.file'       => ''
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testCreate_OneLevel()
    {
        $directory = new Directory(vfsStream::url('root/nonexistent_directory'));
        $directory->create();

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0755, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreate_ThreeLevels()
    {
        $directory = new Directory(vfsStream::url('root/test1/test2/test3'));
        $directory->create();

        $this->assertTrue($this->root->hasChild('test1'));
        $this->assertEquals(0755, $this->root->getChild('test1')->getPermissions());
        $this->assertTrue($this->root->getChild('test1')->hasChild('test2'));
        $this->assertTrue($this->root->getChild('test1')->getChild('test2')->hasChild('test3'));
    }

    public function testCreate_ModeSet777()
    {
        $directory = new Directory(vfsStream::url('root/nonexistent_directory'));
        $directory->create(0777);

        $this->assertTrue($this->root->hasChild('nonexistent_directory'));
        $this->assertEquals(0777, $this->root->getChild('nonexistent_directory')->getPermissions());
    }

    public function testCreate_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new Directory(vfsStream::url('root/nonexistent directory'));
        $directory->create();
    }

    public function testDelete_EmptyDirectory()
    {
        $file = new Directory(vfsStream::url('root/empty_directory'));
        $file->delete();

        $this->assertFalse($this->root->hasChild('empty_directory'));
    }

    public function testDelete_NonexistentDirectory()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\DirectoryNotFoundException');

        $file = new Directory(vfsStream::url('root/nonexistent_directory'));
        $file->delete();
    }

    public function testDelete_SomeFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $directory = new Directory(vfsStream::url('root/some.file'));
        $directory->delete();
    }

    public function testDelete_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new Directory(vfsStream::url('root/empty_directory'));
        $directory->delete();
    }
}
