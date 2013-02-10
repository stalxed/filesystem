<?php
namespace StalxedTest\FileSystem;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\FileObject;
use Stalxed\System\Random;

class FileObjectTest extends \PHPUnit_Framework_TestCase
{
    private $root;

    protected function setUp()
    {
        parent::setUp();

        $structure = array(
            'some.file'        => 'some text',
            'three_lines.file' => "one\ntwo\nthree",
            'empty.file'       => '',
            'directory'        => array()
        );

        $this->root = vfsStream::setup('root', null, $structure);
    }

    public function testIsEmpty_FileEmpty()
    {
        $file = new FileObject(vfsStream::url('root/empty.file'));

        $this->assertTrue($file->isEmpty());
    }

    public function testIsEmpty_FileNotEmpty()
    {
        $file = new FileObject(vfsStream::url('root/some.file'));

        $this->assertFalse($file->isEmpty());
    }

    public function testGetLineCount_FileWithThreeLines()
    {
        $file = new FileObject(vfsStream::url('root/three_lines.file'));

        $this->assertSame(3, $file->getLineCount());
    }

    public function testGetLineCount_EmptyFile()
    {
        $file = new FileObject(vfsStream::url('root/empty.file'));

        $this->assertSame(1, $file->getLineCount());
    }

    public function testGetContents()
    {
        $file = new FileObject(vfsStream::url('root/some.file'));

        $this->assertSame('some text', $file->getContents());
    }

    public function testGetLines()
    {
        $file = new FileObject(vfsStream::url('root/three_lines.file'));

        $this->assertSame(array("one\n", "two\n", 'three'), $file->getLines());
    }

    public function testFindLineByNumber()
    {
        $file = new FileObject(vfsStream::url('root/three_lines.file'));

        $this->assertSame("two\n", $file->findLineByNumber(1));
    }

    public function testFindLineByNumber_LineNotExist()
    {
        $file = new FileObject(vfsStream::url('root/three_lines.file'));

        $this->assertNull($file->findLineByNumber(3));
    }

    public function testFindLineByNumber_EmptyFile()
    {
        $file = new FileObject(vfsStream::url('root/empty.file'));

        $this->assertNull($file->findLineByNumber(0));
    }

    public function testFindRandomLine_FileContainsOneLine()
    {
        $file = new FileObject(vfsStream::url('root/some.file'));

        $this->assertSame("some text", $file->findRandomLine());
    }

    public function testFindLineByNumber_EmptyLine()
    {
        $this->test_fs_helper->filePutContents('test.file', "one\ntwo\nthree\n");

        $file = new File($this->test_fs_helper->getPath('test.file'));

        $this->assertSame('', $file->findLineByNumber(3));
    }

    public function testFindLineByPart()
    {
        $this->test_fs_helper->filePutContents('test.file',
                'first|first line|line' . "\n" .
                'second|second line|line' . "\n" .
                'third|third line|line' . "\n"
        );

        $file = new File($this->test_fs_helper->getPath('test.file'));

        $this->assertSame('second|second line|line', $file->findLineByPart('|', 1, 'second line'));
    }

    public function testFindLineByPart_LineNotExist()
    {
        $this->test_fs_helper->filePutContents('test.file',
                'first|first line|line' . "\n" .
                'second|second line|line' . "\n" .
                'third|third line|line'
        );

        $file = new File($this->test_fs_helper->getPath('test.file'));

        $this->assertNull($file->findLineByPart('|', 1, 'fifth line'));
    }

    public function testFindLineByString()
    {
        $this->test_fs_helper->filePutContents('test.file', "first line\nsecond line\nthird line");

        $file = new File($this->test_fs_helper->getPath('test.file'));

        $this->assertSame('second line', $file->findLineByString('cond li'));
    }

    public function testFindLineByString_FindPositionSet()
    {
        $this->test_fs_helper->filePutContents('test.file', "first line\nsecond line\nthird line");

        $file = new File($this->test_fs_helper->getPath('test.file'));

        $this->assertSame('second line', $file->findLineByString('line', 7));
    }

    public function testFindLineByString_LineNotExist()
    {
        $this->test_fs_helper->filePutContents('test.file', "first line\nsecond line\nthird line");

        $file = new File($this->test_fs_helper->getPath('test.file'));

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

        $file = new FileObject(vfsStream::url('root/three_lines.file'));

        $this->assertSame("two\n", $file->findRandomLine());
    }

    public function testFindRandomLine_EmptyFile()
    {
        $file = new FileObject(vfsStream::url('root/empty.file'));

        $this->assertNull($file->findRandomLine());
    }

    public function testToWritableForAll()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

    public function testCopyTo()
    {
        $file = new FileObject(vfsStream::url('root/some.file'));
        $file->copyTo(vfsStream::url('root/directory'));

        $this->assertTrue($this->root->hasChild('directory'));
        $this->assertTrue($this->root->getChild('directory')->hasChild('some.file'));
        $this->assertEquals(
            'some text',
            $this->root->getChild('directory')->getChild('some.file')->getContent()
        );
    }

    public function testCopyTo_FileNameSet()
    {
        $file = new FileObject(vfsStream::url('root/some.file'));
        $file->copyTo(vfsStream::url('root/directory'), 'new');

        $this->assertTrue($this->root->hasChild('directory'));
        $this->assertTrue($this->root->getChild('directory')->hasChild('new.file'));
        $this->assertEquals(
            'some text',
            $this->root->getChild('directory')->getChild('new.file')->getContent()
        );
    }

    public function testCopyTo_DirectoryDestinationNotExist()
    {
        $this->setExpectedException(
            '\\Stalxed\FileSystem\Exception\RuntimeException',
            'Destination directory is not exist. Path: ' . vfsStream::url('root/directory1') . '.'
        );

        $file = new FileObject(vfsStream::url('root/some.file'));
        $file->copyTo(vfsStream::url('root/directory1'));
    }

    public function testCopyTo_FileAlreadyExists()
    {
        $this->test_fs_helper->createDirectory('from');
        $this->test_fs_helper->filePutContents('from/test.file', '12345');
        $this->test_fs_helper->createDirectory('to');
        $this->test_fs_helper->filePutContents('to/test.file', 'abcde');

        $file = new File($this->test_fs_helper->getPath('from/test.file'));
        $file->copyTo($this->test_fs_helper->getPath('to'));

        $this->test_fs_helper->assertFileExistsAndContains('to/test.file', 'abcde');
    }

    public function testCopyTo_IncorrectMode()
    {
        $this->markTestSkipped('Test not implemented, because for the development use windows.');
    }

}