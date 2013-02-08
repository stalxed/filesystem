<?php
require_once dirname(__FILE__) . '/../../_setup.php';
require_once 'libs/System/Directory.class.php';
require_once 'unit/TestFSHelper.php';

class System_DirectoryTest extends PHPUnit_Framework_TestCase
{
	private $test_fs_helper;
	
	protected function setUp()
	{
		parent::setUp();
		
		$this->test_fs_helper = new TestFSHelper();	
		$this->test_fs_helper->setUp();
	}

    protected function tearDown()
    {
        $this->test_fs_helper->tearDown();
        $this->test_fs_helper = NULL;

		parent::tearDown();
	}
	
	public function testIsExists_DirectoryExists()
	{
		$this->test_fs_helper->createDirectory('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertTrue($directory->isExists());
	}
	
	public function testIsExists_DirectoryNotExist()
	{
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isExists());
	}
	
	public function testIsExists_SetFilePath()
	{
		$this->test_fs_helper->createFile('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isExists());
	}
	
	public function testIsReadable_DirectoryExistsAndReadable()
	{
		$this->test_fs_helper->createDirectory('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertTrue($directory->isReadable());
	}
	
	public function testIsReadable_DirectoryExistsAndNotReadable()
	{
		$this->markTestSkipped('Test not implemented, because for the development use windows.');
	}
	
	public function testIsReadable_DirectoryNotExist()
	{
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isReadable());
	}
	
	public function testIsReadable_SetFilePath()
	{
		$this->test_fs_helper->createFile('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isReadable());
	}
	
	public function testIsWritable_DirectoryExistsAndWritable()
	{
		$this->test_fs_helper->createDirectory('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertTrue($directory->isWritable());
	}
	
	public function testIsWritable_DirectoryExistsAndNotWritable()
	{
		$this->markTestSkipped('Test not implemented, because for the development use windows.');
	}
	
	public function testIsWritable_DirectoryNotExist()
	{
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isWritable());
	}
	
	public function testIsWritable_SetFilePath()
	{
		$this->test_fs_helper->createFile('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		
		$this->assertFalse($directory->isWritable());
	}
	
	public function testIsEmpty_EmptyDirectory()
	{
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertTrue($directory->isEmpty());
	}
	
	public function testIsEmpty_DirectoryContainsFile()
	{
		$this->test_fs_helper->createFile('test.file');
		
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertFalse($directory->isEmpty());
	}
	
	public function testIsEmpty_DirectoryContainsDirectory()
	{
		$this->test_fs_helper->createDirectory('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertFalse($directory->isEmpty());
	}
	
	public function testIsEmpty_DirectoryContainsSubDirectoriesAndFiles()
	{
		$this->test_fs_helper->createDirectory('test1');
		$this->test_fs_helper->createDirectory('test1/test2');
		$this->test_fs_helper->createFile('test1/test2/test3.file');
		
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertFalse($directory->isEmpty());
	}
	
	public function testGetSize()
	{
		$this->test_fs_helper->filePutContents('test1.file', '1');
		$this->test_fs_helper->filePutContents('test2.file', '2');
		$this->test_fs_helper->createDirectory('test');
		$this->test_fs_helper->filePutContents('test/test3.file', '3');
		
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertSame(3, $directory->getSize());
	}
	
	public function testGetSize_EmptyDirectory()
	{
		$directory = new System_Directory($this->test_fs_helper->getPathStore());
		
		$this->assertSame(0, $directory->getSize());
	}
	
	public function testCreate_OneLevel()
	{
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		$directory->create();
		
		$this->test_fs_helper->assertDirectoryExists('test');
	}
	
	public function testCreate_FiveLevels()
	{
		$directory = new System_Directory($this->test_fs_helper->getPath('test1/test2/test3/test4/test5'));
		$directory->create();
		
		$this->test_fs_helper->assertDirectoryExists('test1/test2/test3/test4/test5');
	}
	
	public function testCreate_DirectoryNameContainsIncorrectSymbols()
	{
		$path = $this->test_fs_helper->getPath('*incorrect_name*');
		
		$this->setExpectedException('System_FSException', 'Failed to create directory. Path: ' . $path . '.');
		
		$directory = new System_Directory($path);
    	$directory->create();
	}
	
    public function testCreate_IncorrectMode()
    {
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
	
	public function testDelete_EmpyDirectory()
	{
		$this->test_fs_helper->createDirectory('test');
		
		$directory = new System_Directory($this->test_fs_helper->getPath('test'));
		$directory->delete();
		
		$this->test_fs_helper->assertDirectoryNotExists('test');
	}
	
	public function testDelete_FullDirectory()
	{
	    $this->test_fs_helper->createDirectory('test1');
		$this->test_fs_helper->createDirectory('test1/test2');
		
		$path = $this->test_fs_helper->getPath('test1');
		
		$this->setExpectedException('System_FSException', 'Failed to delete directory. Path: ' . $path . '.');
		
		$directory = new System_Directory($path);
		$directory->delete();
	}
	
    public function testClear()
    {
    	$this->test_fs_helper->createFile('test.file');
    	$this->test_fs_helper->createDirectory('test');
    	$this->test_fs_helper->createFile('test/test.file');
    	
    	$directory = new System_Directory($this->test_fs_helper->getPathStore());
    	$directory->clear();
    	
    	$this->test_fs_helper->assertFileNotExists('test.file');
    	$this->test_fs_helper->assertDirectoryNotExists('test');
    	$this->test_fs_helper->assertFileNotExists('test/test.file');
    }
    
    public function testClear_NoPermissionsToDeleteDirectory()
    {	
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
    
    public function testClear_NoPermissionsToDeleteFile()
    {	
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
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
    	$this->test_fs_helper->createFile('from/test1.file');
    	$this->test_fs_helper->createFile('from/test2.file');
    	$this->test_fs_helper->createDirectory('from/test');
    	$this->test_fs_helper->createFile('from/test/test.file');
        $this->test_fs_helper->createDirectory('to');
        
    	$directory = new System_Directory($this->test_fs_helper->getPath('from'));
    	$directory->copyTo($this->test_fs_helper->getPath('to'));
    	
    	$this->test_fs_helper->assertFileExists('to/test1.file');
    	$this->test_fs_helper->assertFileExists('to/test2.file');
    	$this->test_fs_helper->assertDirectoryExists('to/test');
    	$this->test_fs_helper->assertFileExists('to/test/test.file');
    }
    
    public function testCopyTo_DirectoryDestinationNotExist()
    {
    	$this->test_fs_helper->createDirectory('from');
    	$this->test_fs_helper->createFile('from/test1.file');
    	$this->test_fs_helper->createFile('from/test2.file');
    	$this->test_fs_helper->createDirectory('from/test');
    	$this->test_fs_helper->createFile('from/test/test.file');
    	
    	$path = $this->test_fs_helper->getPath('to');
    	
        $this->setExpectedException('System_FSException', 'Directory destination is not exist. Path: ' . $path . '.');
    	    	        
    	$directory = new System_Directory($this->test_fs_helper->getPath('from'));
    	$directory->copyTo($path);
    }
    
    public function testCopyTo_DirectoriesAndFilesAlreadyExists()
    {
    	$this->test_fs_helper->createDirectory('from');
    	$this->test_fs_helper->filePutContents('from/test1.file', '12345');
    	$this->test_fs_helper->filePutContents('from/test2.file', '678910');
    	$this->test_fs_helper->createDirectory('from/test');
    	$this->test_fs_helper->filePutContents('from/test/test.file', '1112131415');
        $this->test_fs_helper->createDirectory('to');
        $this->test_fs_helper->filePutContents('to/test1.file', 'abcde');
    	$this->test_fs_helper->filePutContents('to/test2.file', 'fghij');
    	$this->test_fs_helper->createDirectory('to/test');
    	$this->test_fs_helper->filePutContents('to/test/test.file', 'fghij');
        
    	$directory = new System_Directory($this->test_fs_helper->getPath('from'));
    	$directory->copyTo($this->test_fs_helper->getPath('to'));

    	$this->test_fs_helper->assertFileExistsAndContains('to/test1.file', 'abcde');
    	$this->test_fs_helper->assertFileExistsAndContains('to/test2.file', 'fghij');
    	$this->test_fs_helper->assertDirectoryExists('to/test');
    	$this->test_fs_helper->assertFileExistsAndContains('to/test/test.file', 'fghij');
    }
    
    public function testCopyTo_NoPermissionsToCreateDirectory()
    {
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
    
    public function testCopyTo_NoPermissionsToCopyingFile()
    {
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
    
    public function testCopyTo_IncorrectDirmode()
    {
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
    
    public function testCopyTo_IncorrectFilemode()
    {
    	$this->markTestSkipped('Test not implemented, because for the development use windows.');
    }
    
    public function testCreateDirectoryIterator()
    {
    	$directory = new System_Directory($this->test_fs_helper->getPathStore());
    	
    	$expected = new DirectoryIterator($this->test_fs_helper->getPathStore());
    	
    	$this->assertEquals($expected, $directory->createDirectoryIterator());
    }
    
    public function testCreateRecursiveDirectoryIterator()
    {
    	$directory = new System_Directory($this->test_fs_helper->getPathStore());
    	
    	$expected = new RecursiveDirectoryIterator($this->test_fs_helper->getPathStore());
    	
    	$this->assertEquals($expected, $directory->createRecursiveDirectoryIterator());
    }
}
?>