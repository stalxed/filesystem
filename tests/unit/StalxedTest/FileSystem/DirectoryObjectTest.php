<?php
namespace StalxedTest\FileSystem;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\DirectoryObject;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class DirectoryObjectTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'some_directory'   => array(
                'sub1' => array(
                    'subsub' => array(
                        'some1.file' => 'some text one',  // 13 bits
                        'some2.file' => 'some text two',  // 13 bits
                        'some3.file' => 'some text three' // 15 bits
                    ),
                ),
                'sub2' => array(
                    'subsub1' => array(
                        'some1.file' => 'some text one',  // 13 bits
                        'some2.file' => 'some text two',  // 13 bits
                        'some3.file' => 'some text three' // 15 bits
                    ),
                    'subsub2' => array(
                        'some1.file' => 'some text one',  // 13 bits
                        'some2.file' => 'some text two',  // 13 bits
                        'some3.file' => 'some text three' // 15 bits
                    )
                ),
            ),
            'empty_directory'  => array()
        );
        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testGetRealPath_DirectoryInFileSystem()
    {
        $directory = new DirectoryObject(__DIR__ . '/././.');

        $this->assertEquals(__DIR__, $directory->getRealPath());
    }

    public function testGetRealPath_DirectoryInVfs()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/'));

        $this->assertEquals($directory->getPathname(), $directory->getRealPath());
    }

    public function testIsEmpty_DirectoryContainingFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/sub1/subsub/'));

        $this->assertFalse($directory->isEmpty());
    }

    public function testIsEmpty_DirectoryContainsSubdirectoriesAndFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/'));

        $this->assertFalse($directory->isEmpty());
    }

    public function testIsEmpty_EmptyDirectory()
    {
        $directory = new DirectoryObject(vfsStream::url('root/empty_directory/'));

        $this->assertTrue($directory->isEmpty());
    }

    public function testGetSize_DirectoryContainingFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/sub1/subsub/'));

        $this->assertEquals(41, $directory->getSize());
    }

    public function testGetSize_DirectoryContainsSubdirectoriesAndFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/'));

        $this->assertEquals(123, $directory->getSize());
    }

    public function testGetSize_EmptyDirectory()
    {
        $directory = new DirectoryObject(vfsStream::url('root/empty_directory/'));

        $this->assertEquals(0, $directory->getSize());
    }

    public function testClear_DirectoryContainingFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/sub1/subsub/'));

        $this->assertEquals(41, $directory->getSize());
    }

    public function testClear_DirectoryContainsSubdirectoriesAndFiles()
    {
         $do = new DirectoryObject(vfsStream::url('root/'));
         $do->clear();

         $this->assertFalse($this->root->hasChildren());
    }

    public function testClear_EmptyDirectory()
    {
        $directory = new DirectoryObject(vfsStream::url('root/empty_directory/'));
        $directory->clear();

        $this->assertFalse($this->root->getChild('empty_directory')->hasChildren());
    }

    public function testClear_DirectoryReadOnly()
    {
        $this->root
            ->getChild('some_directory')
            ->getChild('sub1')
            ->chmod(0555);

        try {
            $directory = new DirectoryObject(vfsStream::url('root/some_directory/sub1/'));
            $directory->clear();
        } catch (\Stalxed\FileSystem\Exception\PermissionDeniedException $e) {
            $sub1 = $this->root
                ->getChild('some_directory')
                ->getChild('sub1');

            $this->assertTrue($sub1->hasChild('subsub'));
            $this->assertFalse($sub1->getChild('subsub')->hasChildren());

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testClear_SubDirectoryReadOnly()
    {
        $this->root
            ->getChild('some_directory')
            ->getChild('sub1')
            ->getChild('subsub')
            ->chmod(0555);

        try {
            $directory = new DirectoryObject(vfsStream::url('root/some_directory/sub1/'));
            $directory->clear();
        } catch (\Stalxed\FileSystem\Exception\PermissionDeniedException $e) {
            $sub1 = $this->root
                ->getChild('some_directory')
                ->getChild('sub1');

            $this->assertTrue($sub1->hasChild('subsub'));
            $this->assertTrue($sub1->getChild('subsub')->hasChild('some1.file'));
            $this->assertTrue($sub1->getChild('subsub')->hasChild('some2.file'));
            $this->assertTrue($sub1->getChild('subsub')->hasChild('some3.file'));

            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCreateDirectoryIterator_DirectoryContainingFiles()
    {
        $directory = new DirectoryObject(vfsStream::url('root/some_directory/'));

        $expected = new \DirectoryIterator(vfsStream::url('root/some_directory/'));
        $expected->setFileClass('Stalxed\FileSystem\FileObject');
        $expected->setInfoClass('Stalxed\FileSystem\FileInfo');
        $this->assertEquals($expected, $directory->createDirectoryIterator());
    }

    private function assertStructureVfs($expected, $actual)
    {
        $expectedVisitor = new vfsStreamStructureVisitor();
        $expectedVisitor->visitDirectory($expected);

        $actualVisitor = new vfsStreamStructureVisitor();
        $actualVisitor->visitDirectory($actual);

        $this->assertEquals($expectedVisitor->getStructure(), $actualVisitor->getStructure());
    }
}
