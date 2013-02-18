<?php
namespace StalxedTest\FileSystem;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\Control;
use Stalxed\FileSystem\DirectoryObject;
use Stalxed\FileSystem\FileInfo;

class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'some_directory'  => array(
                'some1.file' => 'some text one',   // 13 bits
                'some2.file' => 'some text two',   // 13 bits
                'some3.file' => 'some text three'  // 15 bits
            ),
            'empty_directory' => array(),
            'some.file'       => 'some text',      // 9 bits
            'empty.file'      => ''                // 0 bit
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testGetRealPath_DirectoryInFileSystem()
    {
        $fileinfo = new FileInfo(__DIR__ . '/././.');

        $this->assertEquals(__DIR__, $fileinfo->getRealPath());
    }

    public function testGetRealPath_DirectoryInVfs()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some_directory'));

        $this->assertEquals($fileinfo->getPathname(), $fileinfo->getRealPath());
    }

    public function testGetRealPath_FileInFileSystem()
    {
        $fileinfo = new FileInfo(__DIR__ . '/./././' . basename(__FILE__));

        $this->assertEquals(__FILE__, $fileinfo->getRealPath());
    }

    public function testGetRealPath_FileInVfs()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some.file'));

        $this->assertEquals($fileinfo->getPathname(), $fileinfo->getRealPath());
    }

    public function testGetSize_SomeDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some_directory'));

        $this->assertEquals(41, $fileinfo->getSize());
    }

    public function testGetSize_SomeFile()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some.file'));

        $this->assertEquals(9, $fileinfo->getSize());
    }

    public function testGetSize_UnknownPath()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PathNotFoundException');

        $fileinfo = new FileInfo(vfsStream::url('root/nonexistent_directory/nonexistent.file'));
        $fileinfo->getSize();
    }

    public function testIsEmpty_DirectoryWithFiles()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some_directory/'));

        $this->assertFalse($fileinfo->isEmpty());
    }

    public function testIsEmpty_EmptyDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/empty_directory'));

        $this->assertTrue($fileinfo->isEmpty());
    }

    public function testIsEmpty_FileContainsText()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some.file'));

        $this->assertFalse($fileinfo->isEmpty());
    }

    public function testIsEmpty_EmptyFile()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/empty.file'));

        $this->assertTrue($fileinfo->isEmpty());
    }

    public function testIsEmpty_UnknownPath()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PathNotFoundException');

        $fileinfo = new FileInfo(vfsStream::url('root/nonexistent_directory/nonexistent.file'));
        $fileinfo->isEmpty();
    }

    public function testOpenDirectory_SomeDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new DirectoryObject($fileinfo->getRealPath());
        $this->assertEquals($expected, $fileinfo->openDirectory());
    }

    public function testControl_SomeDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new Control\Directory($fileinfo);
        $this->assertEquals($expected, $fileinfo->control());
    }

    public function testControl_SomeFile()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/some.file'));

        $expected = new Control\File($fileinfo);
        $this->assertEquals($expected, $fileinfo->control());
    }

    public function testControl_NonexistentDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/nonexistent_directory'));

        $expected = new Control\Directory($fileinfo);
        $this->assertEquals($expected, $fileinfo->control(FileInfo::TYPE_DIRECTORY));
    }

    public function testControl_NonexistentFile()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/nonexistent.file'));

        $expected = new Control\File($fileinfo);
        $this->assertEquals($expected, $fileinfo->control(FileInfo::TYPE_FILE));
    }

    public function testControl_UnknownPath()
    {
        $this->setExpectedException('Stalxed\FileSystem\Exception\PathNotFoundException');

        $fileinfo = new FileInfo(vfsStream::url('root/nonexistent_directory/nonexistent.file'));
        $fileinfo->control();
    }
}
