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
                'some1.file' => 'some text one',
                'some2.file' => 'some text two',
                'some3.file' => 'some text three'
            ),
            'empty_directory' => array(),
            'some.file'       => 'some text'
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

    public function testGetSize_EmptyDirectory()
    {
        $fileinfo = new FileInfo(vfsStream::url('root/empty_directory'));

        $this->assertEquals(0, $fileinfo->getSize());
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
}
