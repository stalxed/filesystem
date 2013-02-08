<?php
namespace Stalxed\FileSystem;

/**
 * Выполняет различные операции с директорией.
 *
 */
class Directory
{
    /**
     * Путь к директории.
     *
     * @var string
     */
    private $directory_path;
    
    /**
     * Конструктор.
     * Устанавливает путь к директории.
     *
     * @param string $directory_path
     */
    public function __construct($directory_path)
    {
        $this->setDirectoryPath($directory_path);
    }
    
    /**
     * Устанавливает путь к директории.
     *
     * @param string $directory_path
     */
    public function setDirectoryPath($directory_path)
    {
        $this->directory_path = $directory_path;
    }
    
    /**
     * Проверяет, существует ли директория.
     *
     * @return boolean
     */
    public function isExists()
    {
        return is_dir($this->directory_path);
    }
    
    /**
     * Проверяет, доступна ли директория для чтения.
     *
     * @return boolean
     */
    public function isReadable()
    {
        return is_dir($this->directory_path) && is_readable($this->directory_path);
    }
    
    /**
     * Проверяет, доступна ли директория для записи.
     *
     * @return boolean
     */
    public function isWritable()
    {
        return is_dir($this->directory_path) && is_writable($this->directory_path);
    }
    
    /**
     * Проверяет, является ли директория пустой.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $iterator = new \RecursiveIteratorIterator(
            $this->createRecursiveDirectoryIterator(),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        $is_empty = true;
        foreach ($iterator as $item) {
            $is_empty = false;
            
            break;
        }
        
        return $is_empty;
    }
    
    /**
     * Возвращает путь к директории.
     *
     * @return string
     */
    public function getDirectoryPath()
    {
        return $this->directory_path;
    }
    
    /**
     * Возвращает размер директории.
     *
     * @return integer
     */
    public function getSize()
    {
        $iterator = new \RecursiveIteratorIterator(
            $this->createRecursiveDirectoryIterator(),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        $size = 0;
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $size += $item->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Создаёт директорию.
     * Создаёт вложенные папки, указанные в пути к директории,
     * если они не существуют.
     * По умолчанию устанавливает права доступа 0755.
     *
     * @param integer $mode
     * @throws System_FSException
     */
    public function create($mode = 0755)
    {
        if (!@mkdir($this->directory_path, $mode, true)) {
            throw new Exception\RuntimeException('Failed to create directory.', $this->directory_path);
        }
    }
    
    /**
     * Удаляет директорию.
     *
     * @throws System_FSException
     */
    public function delete()
    {
        if (!@rmdir($this->directory_path)) {
            throw new Exception\RuntimeException('Failed to delete directory.', $this->directory_path);
        }
    }
    
    /**
     * Удаляет все вложенные элементы.
     *
     * @throws System_FSException
     */
    public function clear()
    {
        $iterator = new \RecursiveIteratorIterator(
            $this->createRecursiveDirectoryIterator(),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (!@rmdir($item->getPathname())) {
                    throw new Exception\RuntimeException('Failed to delete directory.', $this->directory_path);
                }
            } else {
                if (!@unlink($item->getPathname())) {
                    throw new Exception\RuntimeException('Failed to delete file.', $this->directory_path);
                }
            }
        }
    }
    
    /**
     * Устанавливает права доступа для записи для директории
     * и всех вложенных элементов для всех пользователей.
     *
     * @throws System_FSException
     */
    public function makeWritableForAll()
    {
        $iterator = new \RecursiveIteratorIterator(
            $this->createRecursiveDirectoryIterator(),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if (!@chmod($item->getPathname(), 0777)) {
                throw new Exception\RuntimeException('Failed to change permissions.', $item->getPathname());
            }
        }
        
        if (!@chmod($this->directory_path, 0777)) {
            throw new Exception\RuntimeException('Failed to change permissions.', $this->directory_path);
        }
    }
    
    /**
     * Копирует директорию со всеми вложенными элементами.
     * Если файл назначения уже существует, то его копирование
     * не выполняет. По умолчанию устанавливает права доступа
     * 0755 для директорий и 0644 для файлов.
     *
     * @param string $directory_destination_path
     * @param integer $dirmode
     * @param integer $filemode
     * @throws System_FSException
     */
    public function copyTo($directory_destination_path, $dirmode = 0755, $filemode = 0644)
    {
        if (!is_dir($directory_destination_path)) {
            throw new Exception\RuntimeException('Directory destination is not exist.', $directory_destination_path);
        }
        
        $iterator = $this->createRecursiveDirectoryIterator();
        $iterator->rewind();
        
        $this->coping($iterator, $directory_destination_path, $dirmode, $filemode);
    }
    
    /**
     * Рекурсивная функция для копирования директории со всеми
     * вложенными элементами.
     *
     * @param RecursiveDirectoryIterator $iterator
     * @param string $directory_destination_path
     * @param integer $dirmode
     * @param integer $filemode
     * @throws System_FSException
     */
    private function coping(RecursiveDirectoryIterator $iterator, $directory_destination_path, $dirmode, $filemode)
    {
        foreach ($iterator as $item) {
            $destination_path = $directory_destination_path . '/' . $iterator->getFilename();
            
            if ($iterator->hasChildren()) {
                if ($iterator->isDir() && !is_dir($destination_path)) {
                    if (!@mkdir($destination_path, $dirmode)) {
                        throw new Exception\RuntimeException('Failed to create directory.', $destination_path);
                    }
                }
                
                $this->coping($iterator->getChildren(), $destination_path, $dirmode, $filemode);
            } elseif (!file_exists($destination_path)) {
                if (!@copy($item->getPathname(), $destination_path)) {
                    throw new Exception\RuntimeException(
                        'Failed to copy the file to ' . $destination_path . '.',
                        $item->getPathname()
                    );
                }
                
                if (!@chmod($destination_path, $filemode)) {
                    throw new Exception\RuntimeException('Failed to change permissions.', $destination_path);
                }
            }
        }
    }
    
    /**
     * Создаёт объект DirectoryIterator для текущей директории.
     *
     * @return DirectoryIterator
     */
    public function createDirectoryIterator()
    {
        return new \DirectoryIterator($this->directory_path);
    }
    
    /**
     * Создаёт объект RecursiveDirectoryIterator для текущей директории.
     *
     * @return RecursiveDirectoryIterator
     */
    public function createRecursiveDirectoryIterator()
    {
        return new \RecursiveDirectoryIterator($this->directory_path);
    }
}
