<?php
namespace Stalxed\FileSystem\Exception;

class RuntimeException extends \Exception implements ExceptionInterface
{
	/**
	 * ���� � ���������� ��� �����.
	 *
	 * @var string
	 */
	private $path;
	
	/**
	 * �����������.
	 * ���������� ���������. ������������� ���������, ���� � ���������� ��� ����� �
	 * ��� ����������.
	 *
	 * @param string $message
	 * @param string $path
	 * @param integer $code
	 */
	public function __construct($message = '', $path = '', $code = 0)
	{
		$this->path = $path;
		 
		parent::__construct($this->generateMessage($message), $code);
	}
	
	/**
	 * ���������� ���������.
	 *
	 * @param string $message
	 * @return string
	 */
	private function generateMessage($message = '')
	{
		if ($this->path != '')
			$message .= ' Path: ' . $this->path . '.';
			
		return $message;
	}
	
	/**
	 * ���������� ���� � ���������� ��� �����.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
}
