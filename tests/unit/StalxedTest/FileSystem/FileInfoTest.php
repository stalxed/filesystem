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
            'some_directory' => array(
                'some1.file' => 'some text one',
                'some2.file' => 'some text two',
                'some3.file' => 'some text three'
            ),
            'some.file'      => 'some text'
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testGetRealPath_DirectoryInFileSystem()
    {
        $directory = new FileInfo(__DIR__ . '/././.');

        $this->assertEquals(__DIR__, $directory->getRealPath());
    }

    public function testGetRealPath_DirectoryInVfs()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));

        $this->assertEquals($directory->getPathname(), $directory->getRealPath());
    }

    public function testGetRealPath_FileInFileSystem()
    {
        $directory = new FileInfo(__DIR__ . '/./././' . basename(__FILE__));

        $this->assertEquals(__FILE__, $directory->getRealPath());
    }

    public function testGetRealPath_FileInVfs()
    {
        $directory = new FileInfo(vfsStream::url('root/some.file'));

        $this->assertEquals($directory->getPathname(), $directory->getRealPath());
    }

    public function testOpenDirectory_SomeDirectory()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new DirectoryObject($directory->getRealPath());
        $this->assertEquals($expected, $directory->openDirectory());
    }

    public function testControlDirectory_SomeDirectory()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new Control\Directory($directory->getRealPath());
        $this->assertEquals($expected, $directory->controlDirectory());
    }

    public function testControlFile_SomeFile()
    {
        $file = new FileInfo(vfsStream::url('root/some.file'));

        $expected = new Control\File($file->getRealPath());
        $this->assertEquals($expected, $file->conrolFile());
    }
}
