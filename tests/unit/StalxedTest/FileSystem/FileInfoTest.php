<?php
namespace StalxedTest\FileSystem;

use Stalxed\FileSystem\Control\Directory;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\DirectoryObject;
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

    public function testOpenDirectory_SomeDirectory()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));

        $expected = new DirectoryObject($directory->getRealPath());
        $this->assertEquals($expected, $directory->openDirectory());
    }

    public function testControlDirectory_SomeDirectory()
    {
        $directory = new FileInfo(vfsStream::url('root/some_directory'));
        $directory->controlDirectory();

        $expected = new Directory($directory->getRealPath());
        $this->assertEquals($expected, $directory->controlDirectory());
    }
}
