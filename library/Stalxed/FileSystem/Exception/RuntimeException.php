<?php
namespace Stalxed\FileSystem\Exception;

class RuntimeException extends \Exception implements ExceptionInterface
{
	/**
	 * Путь к директории или файлу.
	 *
	 * @var string
	 */
	private $path;
	
	/**
	 * Конструктор.
	 * Генерирует сообщение. Устанавливает сообщение, путь к директории или файлу и
	 * код исключения.
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
	 * Генерирует сообщение.
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
	 * Возвращает путь к директории или файлу.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}
}
