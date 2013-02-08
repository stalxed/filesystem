<?php
namespace Stalxed\FileSystem;

use Stalxed\System\Random;

/**
 * Выполняет различные операции с файлом.
 *
 */
class File
{
    /**
     * Путь к файлу.
     * 
     * @var string
     */
    private $path;
    
    /**
     * Конструктор.
     * Устанавливает путь к файлу.
     * 
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setPath($path);
    }
    
    /**
     * Устанавливает путь к файлу.
     * 
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
    
    /**
     * Проверяет, существует ли файл.
     * 
     * @return boolean
     */
    public function isExists()
    {
        return is_file($this->path);
    }
    
    /**
     * Проверяет, доступен ли файл для чтения.
     * 
     * @return boolean
     */
    public function isReadable()
    {
        return is_file($this->path) && is_readable($this->path);
    }
    
    /**
     * Проверяет, доступен ли файл для записи.
     * 
     * @return boolean
     */
    public function isWritable()
    {
        return is_file($this->path) && is_writable($this->path);
    }
    
    /**
     * Проверяет, является ли файл пустым.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return ($this->getSize() == 0);
    }
    
    /**
     * Возвращает путь к файлу.
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Возвращает размер файла.
     * 
     * @return integer
     */
    public function getSize()
    {
        return $this->createFileObject()->getSize();
    }
    
    /**
     * Создаёт файл.
     * По умолчанию устанавливает права доступа 0644.
     * 
     * @param integer $mode
     * @throws System_FSException
     */
    public function create($mode = 0644)
    {
        if (!@touch($this->path)) {
            throw new Exception\RuntimeException('Failed to create file.', $this->path);
        }
        
        if (!@chmod($this->path, $mode)) {
            throw new Exception\RuntimeException('Failed to change permissions.', $this->path);
        }
    }
    
    /**
     * Удаляет файл.
     * 
     * @throws System_FSException
     */
    public function delete()
    {
        if (!@unlink($this->path)) {
            throw new Exception\RuntimeException('Failed to delete file.', $this->path);
        }
    }
    
    /**
     * Возвращает количество строчек в файле.
     * 
     * @return integer
     */
    public function getLineCount()
    {
        $file = $this->createFileObject();
        
        $count = 0;
        foreach ($file as $temp) {
            ++$count;
        }
        
        return $count;
    }
    
    /**
     * Читает файл и возвращает содержимое.
     * 
     * @return string
     * @throws System_FSException
     */
    public function getContents()
    {
        $content = @file_get_contents($this->path);
        if ($content === false) {
            throw new Exception\RuntimeException('Failed to read file.', $this->path);
        }
        
        return $content;
    }
    
    /**
     * Читает файл и возвращает содержимое в виде массива.
     * Элементы массива являются строчками файла.
     * Переносы строк удаляются.
     * 
     * @return array
     * @throws System_FSException
     */
    public function getLines()
    {
        $lines = @file($this->path);
        if ($lines === false) {
            throw new Exception\RuntimeException('Failed to read file.', $this->path);
        }
        
        foreach ($lines as $key => $line) {
            $lines[$key] = trim($lines[$key]);
        }
        
        return $lines;
    }
    
    /**
     * Записывает содержимое переменной в начало файла.
     * Если файл не существует, то создаёт его.
     * Если файл существует и содержит данные, то обрезает файл
     * до нулевой длины.
     *
     * @param string $content
     * @throws System_FSException
     */
    public function putContents($content)
    {
        $file = $this->createFileObject('w');
        if (!$file->flock(LOCK_EX)) {
            throw new Exception\RuntimeException('Failed to lock file.', $this->path);
        }
        
        $file->fwrite($content);
        $file->flock(LOCK_UN);
    }
    
    /**
     * Записывает содержимое переменной в конец файла.
     * Если файл не существует, то создаёт его.
     * 
     * @param string $content
     * @throws System_FSException
     */
    public function appendContents($content)
    {
        $file = $this->createFileObject('a');
        if (!$file->flock(LOCK_EX)) {
            throw new Exception\RuntimeException('Failed to lock file.', $this->path);
        }
        
        $file->fwrite($content);
        $file->flock(LOCK_UN);
    }
    
    /**
     * Ищет строчку по номеру.
     * 
     * @param integer $line_number
     * @return string
     */
    public function findLineByNumber($line_number)
    {
        $desired_line = null;
        
        $file = $this->createFileObject();
        try {
            for ($i = 0; $i < $line_number; $i++) {
                $file->fgets();
            }
            
            $desired_line = $file->fgets();
        } catch (\Exception $e) {
            return null;
        }
        
        return $desired_line;
    }
    
    /**
     * Ищет строчку по содержимому части(указывается разделитель
     * частей и номер части).
     * 
     * @param string $delimiter
     * @param integer $part_number
     * @param string $desired_value
     * @return string
     */
    public function findLineByPart($delimiter, $part_number, $desired_value)
    {
        $desired_line = null;
        
        foreach ($this->createFileObject() as $line) {
            $temp_parts = explode($delimiter, $line);
            if (isset($temp_parts[$part_number]) && $temp_parts[$part_number] == $desired_value) {
                $desired_line = $line;
                
                break;
            }
        }
        
        return $desired_line;
    }
    
    /**
     * Ищет строчку по фрагменту строки.
     * Если указан второй аргумент, то проверяется начальная
     * позиция вхождения.
     * 
     * @param string $desired_value
     * @param integer $desired_start_position
     * @return string
     */
    public function findLineByString($desired_value, $desired_start_position = null)
    {
        $desired_line = null;
        
        foreach ($this->createFileObject() as $line) {
            $position = strpos($line, $desired_value);
            if ($position !== false) {
                if ($desired_start_position === null || $position == $desired_start_position) {
                    $desired_line = $line;
                    
                    break;
                }
            }
        }
        
        return $desired_line;
    }
    
    /**
     * Возвращает случайную строчку из файла.
     * 
     * @return string
     */
    public function findRandomLine()
    {
        $desired_line = null;
        
        $file = $this->createFileObject();
        for ($i = 0; !$file->eof(); $i++) {
            $line = $file->fgets();
            
            if (Random::getInstance()->getDigit(0, $i) < 1) {
                $desired_line = $line;
            }
        }
        
        return $desired_line;
    }
    
    /**
     * Устанавливает права доступа для записи для всех
     * пользователей.
     * 
     * @throws System_FSException
     */
    public function makeWritableForAll()
    {
        if (!@chmod($this->path, 0777)) {
            throw new Exception\RuntimeException('Failed to change permissions.', $this->path);
        }
    }
    
    /**
     * Копирует файл.
     * Если файл назначения уже существует, то его копирование
     * не выполняет. Если задан $filename, то файл назначения
     * будет иметь новое имя. По умолчанию устанавливает права
     * доступа 0644. 
     * 
     * @param string $directory_destination_path
     * @param string $filename
     * @param integer $mode
     * @throws System_FSException
     */
    public function copyTo($directory_destination_path, $filename = '', $mode = 0644)
    {
        if (!is_dir($directory_destination_path)) {
            throw new Exception\RuntimeException('Destination directory is not exist.', $directory_destination_path);
        }
        
        $file_destination_path = $directory_destination_path . '/';
        if ($filename == '') {
            $file_destination_path .= pathinfo($this->path, PATHINFO_BASENAME);
        } else {
            $file_destination_path .= $filename . '.' . pathinfo($this->path, PATHINFO_EXTENSION);
        }
        
        if (!file_exists($file_destination_path)) {
            if (!@copy($this->path, $file_destination_path)) {
                throw new Exception\RuntimeException(
                    'Failed to copy the file to ' . $file_destination_path . '.',
                    $this->path
                );
            }
            
            if (!@chmod($this->path, $mode)) {
                throw new Exception\RuntimeException('Failed to change permissions.', $this->path);
            }
        }
    }
    
    /**
     * Создаёт объект SplFileObject для текущего файла с 
     * указанным типом доступа.
     * 
     * @param string $open_mode
     * @return SplFileObject
     */
    public function createFileObject($open_mode = 'r')
    {
        $file_object = new \SplFileObject($this->path, $open_mode);
        $file_object->setFlags(\SplFileObject::DROP_NEW_LINE);
        
        return $file_object;
    }
}
