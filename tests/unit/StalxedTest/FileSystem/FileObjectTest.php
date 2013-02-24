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

    public function testCountLines_FileContainsThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame(3, $file->countLines());
    }

    public function testCountLines_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertSame(1, $file->countLines());
    }

    public function testCountLines_FileContainsEmptyStrings()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame(7, $file->countLines());
    }

    public function testCountLines_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->countLines();

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testCountLines_FlagDropNewLineSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertSame(7, $file->countLines());
    }

    public function testCountLines_AllFlagsSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertSame(2, $file->countLines());
    }

    public function testGetContents_SomeFile()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame('some text', $file->getContents());
    }

    public function testGetContents_FileContainsThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("one\ntwo\nthree", $file->getContents());
    }

    public function testGetContents_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertSame('', $file->getContents());
    }

    public function testGetContents_FileContainsEmptyStrings()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame("first\n\n\nlast\n\n\n", $file->getContents());
    }

    public function testGetContents_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->getContents();

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testGetContents_FlagDropNewLineSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertSame("first\n\n\nlast\n\n\n", $file->getContents());
    }

    public function testGetContents_AllFlagsSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertSame("first\n\n\nlast\n\n\n", $file->getContents());
    }

    public function testGetLines_FileContainsThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame(array("one\n", "two\n", 'three'), $file->getLines());
    }

    public function testGetLines_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertSame(array(''), $file->getLines());
    }

    public function testGetLines_FileContainsEmptyStrings()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame(array("first\n", "\n", "\n", "last\n", "\n", "\n", ''), $file->getLines());
    }

    public function testGetLines_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->getLines();

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testGetLines_FlagDropNewLineSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertSame(array('first', '', '', 'last', '', '', ''), $file->getLines());
    }

    public function testGetLines_AllFlagsSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertSame(array('first', 'last'), $file->getLines());
    }

    public function testFindLineByNumber_FileContainsThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("one\n", $file->findLineByNumber(0));
        $this->assertSame("two\n", $file->findLineByNumber(1));
        $this->assertSame('three', $file->findLineByNumber(2));
        $this->assertNull($file->findLineByNumber(3));
    }

    public function testFindLineByNumber_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertEquals('', $file->findLineByNumber(0));
        $this->assertNull($file->findLineByNumber(1));
    }

    public function testFindLineByNumber_FileContainsEmptyStrings()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame("first\n", $file->findLineByNumber(0));
        $this->assertSame("\n", $file->findLineByNumber(1));
        $this->assertSame("\n", $file->findLineByNumber(2));
        $this->assertSame("last\n", $file->findLineByNumber(3));
        $this->assertSame("\n", $file->findLineByNumber(4));
        $this->assertSame("\n", $file->findLineByNumber(5));
        $this->assertSame('', $file->findLineByNumber(6));
        $this->assertNull($file->findLineByNumber(7));
    }

    public function testFindLineByNumber_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->findLineByNumber(2);

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testFindLineByNumber_FlagDropNewLineSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertSame('first', $file->findLineByNumber(0));
        $this->assertSame('', $file->findLineByNumber(1));
        $this->assertSame('', $file->findLineByNumber(2));
        $this->assertSame('last', $file->findLineByNumber(3));
        $this->assertSame('', $file->findLineByNumber(4));
        $this->assertSame('', $file->findLineByNumber(5));
        $this->assertSame('', $file->findLineByNumber(6));
        $this->assertNull($file->findLineByNumber(7));
    }

    public function testFindLineByNumber_AllFlagsSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertSame('first', $file->findLineByNumber(0));
        $this->assertSame('last', $file->findLineByNumber(1));
        $this->assertNull($file->findLineByNumber(2));
    }

    public function testFindLineByString_FileContainsThreeLines()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("one\n", $file->findLineByString('on'));
        $this->assertSame("two\n", $file->findLineByString('wo'));
        $this->assertSame('three', $file->findLineByString('re'));
        $this->assertNull($file->findLineByString('fifth'));
    }

    public function testFindLineByString_FindPositionSet()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));

        $this->assertSame("one\n", $file->findLineByString('on', 0));
        $this->assertSame("two\n", $file->findLineByString('wo', 1));
        $this->assertSame('three', $file->findLineByString('re', 2));
        $this->assertNull($file->findLineByString('fifth', 0));
    }

    public function testFindLineByString_EmptyFile()
    {
        $this->storage->createFile('empty.file');

        $file = new FileObject($this->storage->getPath('empty.file'));

        $this->assertNull($file->findLineByString('some line'));
    }

    public function testFindLineByString_FileContainsEmptyStrings()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame("first\n", $file->findLineByString('fir'));
        $this->assertSame("last\n", $file->findLineByString('las'));
    }

    public function testFindLineByString_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->findLineByString('hr');

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testFindLineByString_FlagDropNewLineSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertSame('first', $file->findLineByString('fir'));
        $this->assertSame('last', $file->findLineByString('las'));
    }

    public function testFindLineByString_AllFlagsSet()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertSame('first', $file->findLineByString('fir'));
        $this->assertSame('last', $file->findLineByString('las'));
    }

    public function testFindRandomLine_FileContainsOneLine()
    {
        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', 'some text');

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertSame('some text', $file->findRandomLine());
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

    public function testFindRandomLine_FileContainsEmptyStrings()
    {
        $list = array(0, 0, 1, 1, 1, 1, 1);
        Random::setCallbackRandomFunction(
            function ($minDigit, $maxDigit) use (&$list) {
                return array_shift($list);
            }
        );

        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));

        $this->assertEquals("\n", $file->findRandomLine());
    }

    public function testFindRandomLine_PositionAfterCall()
    {
        $this->storage->createFile('three_lines.file');
        $this->storage->filePutContents('three_lines.file', "one\ntwo\nthree");

        $file = new FileObject($this->storage->getPath('three_lines.file'));
        $file->findRandomLine();

        $this->assertSame(0, $file->key());
        $this->assertSame("one\n", $file->current());
    }

    public function testFindRandomLine_FlagDropNewLineSet()
    {
        $list = array(0, 0, 1, 1, 1, 1, 1);
        Random::setCallbackRandomFunction(
            function ($minDigit, $maxDigit) use (&$list) {
                return array_shift($list);
            }
        );

        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE);

        $this->assertEquals('', $file->findRandomLine());
    }

    public function testFindRandomLine_AllFlagsSet()
    {
        $list = array(0, 0);
        Random::setCallbackRandomFunction(
            function ($minDigit, $maxDigit) use (&$list) {
                return array_shift($list);
            }
        );

        $this->storage->createFile('some.file');
        $this->storage->filePutContents('some.file', "first\n\n\nlast\n\n\n");

        $file = new FileObject($this->storage->getPath('some.file'));
        $file->setFlags($file::DROP_NEW_LINE | $file::READ_AHEAD | $file::SKIP_EMPTY);

        $this->assertEquals('last', $file->findRandomLine());
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
