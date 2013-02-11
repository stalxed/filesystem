<?php
namespace StalxedTest\FileSystem;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\DirectoryObject;

class DirectoryObjectTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'directory containing files' => array(
                'some1.file'  => 'some text 1',
                'some2.file'  => 'some text 2',
                'some3.file'  => 'some text 3',
            ),
            'directory containing directories' => array(
                'directory 1' => array(),
                'directory 2' => array(),
                'directory 3' => array()
            ),
            'directory containing directories and files' => array(
                'directory 1' => array(),
                'directory 2' => array(),
                'directory 3' => array(),
                'some1.file'  => 'some text 1',
                'some2.file'  => 'some text 2',
                'some3.file'  => 'some text 3',
            ),
            'empty directory' => array()
        );

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testIsEmpty_DirectoryContainsFiles()
    {
        $do = new DirectoryObject(vfsStream::url('root/directory containing files'));

        $this->assertFalse($do->isEmpty());
    }

    public function testIsEmpty_DirectoryContainsDirectories()
    {
        $do = new DirectoryObject(vfsStream::url('root/directory containing directories'));

        $this->assertFalse($do->isEmpty());
    }

    public function testIsEmpty_DirectoryContainsDirectoriesAndFiles()
    {
        $do = new DirectoryObject(vfsStream::url('root/directory containing directories and files'));

        $this->assertFalse($do->isEmpty());
    }

    public function testIsEmpty_EmptyDirectory()
    {
        $do = new DirectoryObject(vfsStream::url('root/empty directory'));

        $this->assertTrue($do->isEmpty());
    }

    public function testGetSize()
    {
        $do = new DirectoryObject(vfsStream::url('root/directory containing directories and files'));

        $this->assertSame(33, $do->getSize());
    }

    public function testGetSize_EmptyDirectory()
    {
        $do = new DirectoryObject(vfsStream::url('root/empty directory'));

        $this->assertSame(0, $do->getSize());
    }

    public function testClear()
    {
         $do = new DirectoryObject(vfsStream::url('root'));
         $do->clear();

         $this->assertFalse($this->root->hasChildren());
    }

    public function testClear_NoPermissionsToDeleteDirectory()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

    public function testClear_NoPermissionsToDeleteFile()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

    public function testToWritableForAll()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

    public function testToWritableForAll_NoPermissionsToChangeMode()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

    public function testCopyTo()
    {
        $directory = new DirectoryObject(vfsStream::url('root/directory containing directories and files'));
        $directory->copyTo(vfsStream::url('root/empty directory'));

        $directory = $this->root->getChild('empty directory');
        $this->assertTrue($directory->hasChild('directory 1'));
        $this->assertTrue($directory->hasChild('directory 2'));
        $this->assertTrue($directory->hasChild('directory 3'));
        $this->assertTrue($directory->hasChild('some1.file'));
        $this->assertTrue($directory->hasChild('some2.file'));
        $this->assertTrue($directory->hasChild('some3.file'));
    }

    public function testCreateDirectoryIterator()
    {
        $directory = new DirectoryObject(vfsStream::url('root/directory containing directories and files'));

        $expected = new \DirectoryIterator(vfsStream::url('root/directory containing directories and files'));
        $expected->setFileClass('Stalxed\FileSystem\FileObject');
        $expected->setInfoClass('Stalxed\FileSystem\FileInfo');
        $this->assertEquals($expected, $directory->createDirectoryIterator());
    }
}
