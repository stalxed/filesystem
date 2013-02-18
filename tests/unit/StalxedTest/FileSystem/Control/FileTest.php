<?php
namespace StalxedTest\FileSystem\Control;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\Control\File;
use Stalxed\FileSystem\FileInfo;

class FileTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'some_directory' => array(
                'some1.file' => 'some text one',
                'some2.file' => 'some text two',
                'some3.file' => 'some text three'
            ),
            'some.file'      => 'some text',
            'empty.file'     => ''
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    /**
     * @requires PHP 5.4
     *
     */
    public function testCreate_NonexistentFile()
    {
        $file = new File(new FileInfo(vfsStream::url('root/nonexistent.file')));
        $file->create();

        $this->assertTrue($this->root->hasChild('nonexistent.file'));
        $this->assertEquals(0644, $this->root->getChild('nonexistent.file')->getPermissions());
    }

    /**
     * @requires PHP 5.4
     *
     */
    public function testCreate_ModeSet777()
    {
        $file = new File(new FileInfo(vfsStream::url('root/nonexistent.file')));
        $file->create(0777);

        $this->assertTrue($this->root->hasChild('nonexistent.file'));
        $this->assertEquals(0777, $this->root->getChild('nonexistent.file')->getPermissions());
    }

    public function testCreate_ParentDirectoryNotExist()
    {
        $this->setExpectedException('Stalxed\FileSystem\Control\Exception\DirectoryNotFoundException');

        $file = new File(new FileInfo(vfsStream::url('root/nonexistent_directory/nonexistent.file')));
        $file->create();
    }

    public function testCreate_FileAlreadyExists()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $file = new File(new FileInfo(vfsStream::url('root/some.file')));
        $file->create();
    }

    public function testCreate_DirectoryAlreadyExists()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\LogicException');

        $file = new File(new FileInfo(vfsStream::url('root/some_directory/')));
        $file->create();
    }

    public function testCreate_ParentDirectoryReadOnly()
    {
        $this->markTestSkipped('Limitation of the current version of the file system.');
    }

    public function testDelete_FileContainingText()
    {
        $file = new File(new FileInfo(vfsStream::url('root/some.file')));
        $file->delete();

        $this->assertFalse($this->root->hasChild('some.file'));
    }

    public function testDelete_EmptyFile()
    {
        $file = new File(new FileInfo(vfsStream::url('root/empty.file')));
        $file->delete();

        $this->assertFalse($this->root->hasChild('empty.file'));
    }

    public function testDelete_NonexistentFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Control\Exception\FileNotFoundException');

        $file = new File(new FileInfo(vfsStream::url('root/nonexistent.file')));
        $file->delete();
    }

    public function testDelete_DirectoryInsteadFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Control\Exception\FileNotFoundException');

        $directory = new File(new FileInfo(vfsStream::url('root/some_directory')));
        $directory->delete();
    }

    public function testDelete_ParentDirectoryReadOnly()
    {
        $this->setExpectedException('Stalxed\FileSystem\Control\Exception\PermissionDeniedException');

        $this->root->chmod(0555);

        $directory = new File(new FileInfo(vfsStream::url('root/some.file')));
        $directory->delete();
    }

    public function testChmod_FileContainingText()
    {
        $this->root->getChild('some.file')->chmod(0555);

        $file = new File(new FileInfo(vfsStream::url('root/some.file')));
        $file->chmod(0777);

        $this->assertEquals(0777, $this->root->getChild('some.file')->getPermissions());
    }

    public function testChmod_EmptyFile()
    {
        $this->root->getChild('empty.file')->chmod(0555);

        $file = new File(new FileInfo(vfsStream::url('root/empty.file')));
        $file->chmod(0777);

        $this->assertEquals(0777, $this->root->getChild('empty.file')->getPermissions());
    }

    public function testChmod_NonexistentFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\FileNotFoundException');

        $file = new File(new FileInfo(vfsStream::url('root/nonexistent.file')));
        $file->chmod(0777);
    }

    public function testChmod_DirectoryInsteadFile()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\FileNotFoundException');

        $file = new File(new FileInfo(vfsStream::url('root/some_directory/')));
        $file->chmod(0777);
    }

    public function testChmod_ParentDirectoryReadOnly()
    {
        $this->markTestSkipped('Limitation of the current version of the file system.');
    }
}
