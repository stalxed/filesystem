<?php
namespace Stalxed\FileSystem;

use Stalxed\System\Random;

class FileObject extends \SplFileObject
{
    public function __construct($filename, $openMode = 'r', $useIncludePath = 'false')
    {
        $this->setFileClass('Stalxed\FileSystem\FileObject');
        $this->setInfoClass('Stalxed\FileSystem\FileInfo');

        parent::__construct($filename, $openMode, $useIncludePath);
    }

    /**
     * (non-PHPdoc)
     *
     * @see SplFileObject::getRealPath()
     */
    public function getRealPath()
    {
        if (in_array('vfs', stream_get_wrappers())) {
            return $this->getPathname();
        }

        return parent::getRealPath();
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
     * Возвращает количество строчек в файле.
     *
     * @return integer
     */
    public function getLineCount()
    {
        $count = 0;
        foreach ($this as $temp) {
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
        $content = '';
        while (! $this->eof()) {
            $content .= $this->fgets();
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
        $lines = array();
        foreach ($this as $key => $line) {
            $lines[$key] = $line;
        }

        return $lines;
    }

    /**
     * Записывает содержимое переменной в конец файла.
     * Если файл не существует, то создаёт его.
     *
     * @param string $content
     * @throws System_FSException
     */
    public function safeWrite($content)
    {
        //for () {
        if ($this->flock(LOCK_EX | LOCK_NB)) {
            $this->fwrite($content);
            $this->flock(LOCK_UN);
        }
        // }

        throw new Exception\RuntimeException('Failed to lock file.', $this->path);
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

        $this->rewind();

        try {
            for ($i = 0; $i < $line_number; $i++) {
                $this->fgets();
            }

            $desired_line = $this->fgets();
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

        foreach ($this as $line) {
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

        foreach ($this as $line) {
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

        $random = new Random();

        for ($i = 0; !$this->eof(); $i++) {
            $line = $this->fgets();

            if ($random->generateNumber(0, $i) < 1) {
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
        if (!@chmod($this->getRealPath(), 0777)) {
            throw new Exception\RuntimeException('Failed to change permissions.', $this->getRealPath());
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
            throw new Exception\RuntimeException();
        }

        $file_destination_path = $directory_destination_path . '/';
        if ($filename == '') {
            $file_destination_path .= $this->getBasename();
        } else {
            $file_destination_path .= $filename . '.' . $this->getExtension();
        }

        if (!file_exists($file_destination_path)) {
            if (!@copy($this->getRealPath(), $file_destination_path)) {
                throw new Exception\RuntimeException();
            }

            if (!@chmod($this->getRealPath(), $mode)) {
                throw new Exception\RuntimeException();
            }
        }
    }
}
