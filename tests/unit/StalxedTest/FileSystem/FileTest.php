<?php
namespace StalxedTest\FileSystem;

use org\bovigo\vfs\vfsStream;
use Stalxed\FileSystem\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
	private $root;
	
	protected function setUp()
	{
		parent::setUp();
		
		$structure = array(
		    'files' => array(
		    	'some.file'           => 'some text',
		    	'three_lines.file'    => "one\ntwo\nthree",
		        'empty.file'          => '',
		    	'not_readable.file'   => 'some text',
		    	'not_writable.file'   => 'some_text'
		    ),
		    'directories' => array(
		    	'some directory'      => array('one' => array(), 'two' => 'some text'),
		        'empty'               => array(),
		    	'not_readable'        => array(),
		    	'not_writable'        => array(),
		    	'directory.with.dots' => array()	
		    )
		);
		
		$this->root = vfsStream::setup('root', null, $structure);
		
		$this->root->getChild('files')->getChild('not_readable.file')->chmod(0222);
		$this->root->getChild('files')->getChild('not_writable.file')->chmod(0444);
		
		$this->root->getChild('directories')->getChild('not_readable')->chmod(0333);
		$this->root->getChild('directories')->getChild('not_writable')->chmod(0555);
	}
	
	public function testIsExists_FileExists()
	{
		$file = new File(vfsStream::url('root/files/some.file'));
		
        $this->assertTrue($file->isExists());
	}
	
	public function testIsExists_FileNotExist()
	{
		$file = new File(vfsStream::url('root/files/nonexistent.file'));
		
        $this->assertFalse($file->isExists());
	}
	
	public function testIsExists_SetDirectoryPath()
	{
		$file = new File(vfsStream::url('/root/directories/some.directory'));
		
        $this->assertFalse($file->isExists());
	}
	
	public function testIsReadable_FileExistsAndReadable()
	{
		$file = new File(vfsStream::url('root/files/empty.file'));
		
        $this->assertTrue($file->isReadable());
	}
	
	public function testIsReadable_FileExistsAndNotReadable()
	{
		$file = new File(vfsStream::url('root/files/not_readable.file'));
		
        $this->assertFalse($file->isReadable());
	}
	
	public function testIsReadable_FileNotExist()
	{
		$file = new File(vfsStream::url('root/files/nonexistent.file'));
		
        $this->assertFalse($file->isReadable());
	}
	
	public function testIsReadable_SetDirectoryPath()
	{
		$file = new File(vfsStream::url('root/directories/some.directory'));
		
        $this->assertFalse($file->isReadable());
	}
	
	public function testIsWritable_FileExistsAndWritable()
	{
		$file = new File(vfsStream::url('root/files/empty.file'));
		
        $this->assertTrue($file->isWritable());
	}
	
	public function testIsWritable_FileExistsAndNotWritable()
	{
		$file = new File(vfsStream::url('root/files/not_writable.file'));
		
        $this->assertFalse($file->isWritable());
	}
	
	public function testIsWritable_FileNotExist()
	{
		$file = new File(vfsStream::url('root/files/nonexistent.file'));
		
        $this->assertFalse($file->isWritable());
	}
	
	public function testIsWritable_SetDirectoryPath()
	{
		$file = new File(vfsStream::url('root/directories/some.directory'));
		
        $this->assertFalse($file->isWritable());
	}
	
	public function testIsEmpty_FileEmpty()
	{
		$file = new File(vfsStream::url('root/files/empty.file'));
		
		$this->assertTrue($file->isEmpty());
	}
	
	public function testIsEmpty_FileNotEmpty()
	{
		$file = new File(vfsStream::url('root/files/some.file'));
		
		$this->assertFalse($file->isEmpty());
	}

	public function testGetSize()
	{
		$file = new File(vfsStream::url('root/files/some.file'));
		
		$this->assertSame(9, $file->getSize());
	}
	
	public function testGetSize_EmptyFile()
	{
		$file = new File(vfsStream::url('root/files/empty.file'));
		
		$this->assertSame(0, $file->getSize());
	}

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
	
	public function testGetLineCount()
	{
		$file = new File(vfsStream::url('root/files/three_lines.file'));
		
		$this->assertSame(3, $file->getLineCount());
	}
	
	public function testGetLineCount_EmptyFile()
	{
		$file = new File(vfsStream::url('root/files/empty.file'));
		
		$this->assertSame(1, $file->getLineCount());
	}
	
    public function testGetContents()
    {
		$file = new File(vfsStream::url('root/files/some.file'));
        
        $this->assertSame('some text', $file->getContents());
    }
     
    public function testGetContents_FileNotExist()
    {
    	$this->setExpectedException(
    		'\\Stalxed\FileSystem\Exception\RuntimeException',
    		'Failed to read file. Path: ' . vfsStream::url('root/files/nonexistent.file') . '.'
    	);
    	
        $file = new File(vfsStream::url('root/files/nonexistent.file'));
        $file->getContents();
    }
    
    public function testGetLines()
    {
		$file = new File(vfsStream::url('root/files/three_lines.file'));

        $this->assertSame(array('one', 'two', 'three'), $file->getLines());
    }
    
    public function testGetLines_FileNotExist()
    {
    	$this->setExpectedException(
    	    '\\Stalxed\FileSystem\Exception\RuntimeException',
    		'Failed to read file. Path: ' . vfsStream::url('root/files/nonexistent.file') . '.'
    	);
    	
        $file = new File(vfsStream::url('root/files/nonexistent.file'));
        $file->getLines();
    }
   
    public function testPutContents_FileExists()
    {
    	$file = new File(vfsStream::url('root/files/some.file'));
        $file->putContents("\nsecond line\nthird line");
        
        $this->assertTrue($this->root->hasChild('files'));
		$this->assertTrue($this->root->getChild('files')->hasChild('some.file'));
		$this->assertEquals(
		    "\nsecond line\nthird line",
			$this->root->getChild('files')->getChild('some.file')->getContent()
		);
    }
    
    public function testPutContents_FileNotExist()
    {
    	$file = new File(vfsStream::url('root/files/nonexistent.file'));
        $file->putContents("first line\nsecond line\nthird line");
        
        $this->assertTrue($this->root->hasChild('files'));
		$this->assertTrue($this->root->getChild('files')->hasChild('nonexistent.file'));
		$this->assertEquals(
		    "first line\nsecond line\nthird line",
			$this->root->getChild('files')->getChild('nonexistent.file')->getContent()
		);
    }
    
    public function testPutContents_EmptyFile()
    {
    	$file = new File(vfsStream::url('root/files/empty.file'));
        $file->putContents("first line\nsecond line\nthird line");
        
        $this->assertTrue($this->root->hasChild('files'));
		$this->assertTrue($this->root->getChild('files')->hasChild('empty.file'));
		$this->assertEquals(
		    "first line\nsecond line\nthird line",
			$this->root->getChild('files')->getChild('empty.file')->getContent()
		);
    } 
    
    public function testPutContents_NotLockableFile()
    {
    	return;
    	
    	$path = $this->test_fs_helper->getPath('test.file');
    	
    	$this->setExpectedException('System_FSException', 'Failed to lock file. Path: ' . $path . '.');
    	
    	$this->test_fs_helper->createFile('test.file');
    	
    	$file_object_mock = $this->getMock('SplFileObject', array('flock'), array(), '', FALSE);
        $file_object_mock->expects($this->any())
             ->method('flock')
             ->will($this->returnValue(FALSE));
        System_FileOFT::setFileObjectMock($file_object_mock);
        
    	$file = new System_FileOFT($path);
        $file->putContents("first line\nsecont line\nthird line");
    }   
     
    public function testAppendContents_FileExists()
    {
    	$this->test_fs_helper->filePutContents('test.file', 'first line');
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        $file->appendContents("\nsecond line\nthird line");
        
        $this->test_fs_helper->assertFileExistsAndContains('test.file', "first line\nsecond line\nthird line");
    }
    
    public function testAppendContents_FileNotExist()
    {
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        $file->appendContents("first line\nsecond line\nthird line");
        
        $this->test_fs_helper->assertFileExistsAndContains('test.file', "first line\nsecond line\nthird line");
    }
    
    public function testAppendContents_EmptyFile()
    {
    	$this->test_fs_helper->createFile('test.file');
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        $file->appendContents("first line\nsecond line\nthird line");

        $this->test_fs_helper->assertFileExistsAndContains('test.file', "first line\nsecond line\nthird line");
    } 

    public function testAppendContents_NotLockableFile()
    {    	
    	$this->test_fs_helper->createFile('test.file');
    	$path = $this->test_fs_helper->getPath('test.file');
    	
    	$this->setExpectedException(
    	    '\\Stalxed\FileSystem\Exception\RuntimeException',
    		'Failed to lock file. Path: ' . $path . '.'
    	);
    	
    	$file_object_mock = $this->getMockBuilder('\SplFileObject')
    	    ->setMethods(array('flock'))
    	    ->setConstructorArgs(array($path))
    	    ->getMock();
        $file_object_mock->expects($this->any())
             ->method('flock')
             ->will($this->returnValue(FALSE));
        System_FileOFT::setFileObjectMock($file_object_mock);
             
    	$file = new System_FileOFT($path);
        $file->appendContents("first line\nsecont line\nthird line");
    } 
    
    public function testFindLineByNumber()
    {
    	$this->test_fs_helper->filePutContents('test.file', "one\ntwo\nthree");
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        
    	$this->assertSame('two', $file->findLineByNumber(1));
    }

    public function testFindLineByNumber_LineNotExist()
    {
    	$this->test_fs_helper->filePutContents('test.file', "one\ntwo\nthree");
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        
    	$this->assertNull($file->findLineByNumber(3));
    }
    
    public function testFindLineByNumber_EmptyFile()
    {
    	$this->test_fs_helper->createFile('test.file');
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
        
    	$this->assertSame('', $file->findLineByNumber(0));
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

	public function testFindRandomLine_FileContainsOneLine()
	{
    	$this->test_fs_helper->filePutContents('test.file', 'first line');
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
		
        $this->assertSame('first line', $file->findRandomLine());
	}
	
	public function testFindRandomLine_FileContainsThreeLines()
	{
		//System_RandomMock::register();
		//System_RandomMock::setDigits(0, 0, 1);
		
		$this->test_fs_helper->filePutContents('test.file', "first line\nsecond line\nthird line");
		
    	$file = new File($this->test_fs_helper->getPath('test.file'));
		
        $this->assertSame('second line', $file->findRandomLine());
	}
	
	public function testFindRandomLine_EmptyFile()
	{
    	$this->test_fs_helper->createFile('test.file');
    	
    	$file = new File($this->test_fs_helper->getPath('test.file'));
		
        $this->assertSame('', $file->findRandomLine());
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
    	$this->test_fs_helper->createDirectory('from');
    	$this->test_fs_helper->createFile('from/test.file');
        $this->test_fs_helper->createDirectory('to');
        
    	$file = new File($this->test_fs_helper->getPath('from/test.file'));
    	$file->copyTo($this->test_fs_helper->getPath('to'));
    	
    	$this->test_fs_helper->assertFileExists('to/test.file');
    }
    
    public function testCopyTo_FileNameContainsIncorrectSymbols()
	{
		$this->test_fs_helper->createFile('test.file');
		$path = $this->test_fs_helper->getPath('test.file');
		$path_new_file = $this->test_fs_helper->getPathStore() . '/*test*.file';
		
		$this->setExpectedException(
			'\\Stalxed\FileSystem\Exception\RuntimeException',
			'Failed to copy the file to ' . $path_new_file . '. Path: ' . $path . '.'
		);
		
		$file = new File($path);
    	$file->copyTo($this->test_fs_helper->getPathStore(), '*test*');
	}
    
    public function testCopyTo_FileNameSet()
    {
    	$this->test_fs_helper->createDirectory('from');
    	$this->test_fs_helper->createFile('from/test.file');
        $this->test_fs_helper->createDirectory('to');
        
    	$file = new File($this->test_fs_helper->getPath('from/test.file'));
    	$file->copyTo($this->test_fs_helper->getPath('to'), 'new_test');
    	
    	$this->test_fs_helper->assertFileExists('to/new_test.file');
    }
    
    public function testCopyTo_DirectoryDestinationNotExist()
    {
    	$this->test_fs_helper->createDirectory('from');
    	$this->test_fs_helper->createFile('from/test.file');
    	$path = $this->test_fs_helper->getPath('to');
    	
    	$this->setExpectedException(
    		'\\Stalxed\FileSystem\Exception\RuntimeException',
    		'Destination directory is not exist. Path: ' . $path . '.'
    	);
        
    	$file = new File($this->test_fs_helper->getPath('from/test.file'));
    	$file->copyTo($path);
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
    
	public function testCreateFileObject()
	{
		$this->test_fs_helper->createFile('test.file');
		
		$file = new File($this->test_fs_helper->getPath('test.file'));
		
		$expected = new \SplFileObject($this->test_fs_helper->getPath('test.file'));
        $expected->setFlags(\SplFileObject::DROP_NEW_LINE);
        
        $this->assertEquals($expected, $file->createFileObject());
	}
	
	public function testCreateFileObject_OpenModeWrite()
	{
		$this->test_fs_helper->createFile('test.file');
		
		$file = new File($this->test_fs_helper->getPath('test.file'));
		
		$expected = new \SplFileObject($this->test_fs_helper->getPath('test.file'), 'w');
        $expected->setFlags(\SplFileObject::DROP_NEW_LINE);
        
        $this->assertEquals($expected, $file->createFileObject('w'));
	}
}
?>