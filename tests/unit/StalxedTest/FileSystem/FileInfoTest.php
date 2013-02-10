<?php

require_once 'library\Stalxed\FileSystem\FileInfo.php';

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * FileInfo test case.
 */
class FileInfoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @requires PHP 5.4
     *
     */
    public function testCreate()
    {
        $file = new File(vfsStream::url('root/files/nonexistent.file'));
        $file->create();

        $this->assertTrue($this->root->hasChild('files'));
        $this->assertTrue($this->root->getChild('files')->hasChild('nonexistent.file'));
    }

    public function testCreate_FileNameContainsIncorrectSymbols()
    {
        return;
        $path = $this->test_fs_helper->getPath('*test*.file');

        $this->setExpectedException(
                '\\Stalxed\FileSystem\Exception\RuntimeException',
                'Failed to create file. Path: ' . $path . '.'
        );

        $file = new File($path);
        $file->create();
    }

    public function testCreate_IncorrectMode()
    {
        $file = new File(vfsStream::url('root/directories/not_writable'));
        $file->create();
    }

    public function testDelete()
    {
        $file = new File(vfsStream::url('root/files/some.file'));
        $file->delete();

        $this->assertTrue($this->root->hasChild('files'));
        $this->assertFalse($this->root->getChild('files')->hasChild('some.file'));
    }

    public function testDelete_FileNotExist()
    {
        $this->setExpectedException(
                '\\Stalxed\FileSystem\Exception\RuntimeException',
                'Failed to delete file. Path: ' . vfsStream::url('root/files/nonexistent.file') . '.'
        );

        $file = new File(vfsStream::url('root/files/nonexistent.file'));
        $file->delete();
    }
}

