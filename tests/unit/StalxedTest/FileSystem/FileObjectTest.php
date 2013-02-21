<?php
namespace StalxedTest\FileSystem;

use Stalxed\FileSystem\FileObject;
use Stalxed\System\Random;
use StalxedTest\FileSystem\TestHelper\Storage;

class FileObjectTest extends \PHPUnit_Framework_TestCase
{
    private $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = new Storage();
    }

    protected function tearDown()
    {
        $this->storage->tearDown();

        parent::tearDown();
    }

    public function testIsEmpty_FileNotEmpty()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertFalse($file->isEmpty());
    }

    public function testIsEmpty_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertTrue($file->isEmpty());
    }

    public function testGetLineCount_FileWithThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame(3, $file->getLineCount());
    }

    public function testGetLineCount_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertSame(1, $file->getLineCount());
    }

    public function testGetContents()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame('some text', $file->getContents());
    }

    public function testGetLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame(array("one\n", "two\n", 'three'), $file->getLines());
    }

    public function testFindLineByNumber()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("two\n", $file->findLineByNumber(1));
    }

    public function testFindLineByNumber_LineNotExist()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertNull($file->findLineByNumber(3));
    }

    public function testFindLineByNumber_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertEquals('', $file->findLineByNumber(0));
    }

    public function testFindRandomLine_FileContainsOneLine()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame('some text', $file->findRandomLine());
    }

    public function testFindLineByNumber_EmptyLine()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertNull($file->findLineByNumber(3));
    }

    public function testFindLineByString()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame('three', $file->findLineByString('hr'));
    }

    public function testFindLineByString_FindPositionSet()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame('three', $file->findLineByString('hr', 1));
    }

    public function testFindLineByString_LineNotExist()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertNull($file->findLineByString('fifth'));
    }

    public function testFindRandomLine_FileContainsThreeLines()
    {
        $list = array(0, 0, 1);
        Random::setCallbackRandomFunction(
            function ($minDigit, $maxDigit) use (&$list) {
                return array_shift($list);
            }
        );

        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("two\n", $file->findRandomLine());
    }

    public function testFindRandomLine_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertEquals('', $file->findRandomLine());
    }

    public function testCopyTo()
    {
        $this->storage->createDirectory('from/');
        $this->storage->createFile('from/some.file');
        $this->storage->createDirectory('to/');

        $file = new FileObject($this->storage->getPath('from/some.file'));
        $file->copyTo($this->storage->getPath('to/some.file'));

        $this->storage->fileExists('to/some.txt');
    }
}
