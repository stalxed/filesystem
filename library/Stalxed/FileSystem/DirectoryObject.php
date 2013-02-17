<?php
namespace Stalxed\FileSystem;

class DirectoryObject extends \SplFileInfo
{
    public function __construct($filename)
    {
        $this->setFileClass('Stalxed\FileSystem\FileObject');
        $this->setInfoClass('Stalxed\FileSystem\FileInfo');

        parent::__construct($filename);
    }

    public function getRealPath()
    {
        if (parse_url($this->getPathname(), PHP_URL_SCHEME) == 'vfs') {
            return $this->getPathname();
        }

        return parent::getRealPath();
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
     * Checks whether the directory is empty.
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
     * Recursively delete subdirectories and files.
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
            $item->getFileInfo()->control()->delete();
        }
    }

    public function chmodInternalContent($mode)
    {

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

        mkdir($directory_destination_path . '/' . $this->getBasename());

        $iterator = $this->createRecursiveDirectoryIterator();
        $iterator->rewind();

        $this->coping($iterator, $directory_destination_path . '/' . $this->getBasename(), $dirmode, $filemode);
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
    private function coping(\RecursiveDirectoryIterator $iterator, $directory_destination_path, $dirmode, $filemode)
    {
        foreach ($iterator as $item) {
            $destination = new FileInfo($directory_destination_path . '/' . $iterator->getFilename());

            if ($iterator->hasChildren()) {
                if ($iterator->isDir() && ! $destination->isDir()) {
                    $destination->controlDirectory()->create($dirmode);
                }

                $this->coping($iterator->getChildren(), $destination->getRealPath(), $dirmode, $filemode);
            } elseif (! $destination->isFile()) {
                $item->getFileInfo()->openFile()->copyTo(
                    $destination->getRealPath()
                );
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
        $iterator = new \DirectoryIterator($this->getRealPath());
        $iterator->setFileClass('Stalxed\FileSystem\FileObject');
        $iterator->setInfoClass('Stalxed\FileSystem\FileInfo');

        return $iterator;
    }

    public function createFilesystemIterator($flags = null)
    {
        if (isset($flags)) {
            $iterator = new \FilesystemIterator($this->directory_path, $flags);
        } else {
            $iterator = new \FilesystemIterator($this->directory_path);
        }
        $iterator->setFileClass('Stalxed\FileSystem\FileObject');
        $iterator->setInfoClass('Stalxed\FileSystem\FileInfo');

        return $iterator;
    }

    public function createGlobIterator($flags)
    {
        if (isset($flags)) {
            $iterator = new \GlobIterator($this->directory_path, $flags);
        } else {
            $iterator = new \GlobIterator($this->directory_path);
        }
        $iterator->setFileClass('Stalxed\FileSystem\FileObject');
        $iterator->setInfoClass('Stalxed\FileSystem\FileInfo');

        return $iterator;
    }

    /**
     * Создаёт объект RecursiveDirectoryIterator для текущей директории.
     *
     * @return RecursiveDirectoryIterator
     */
    public function createRecursiveDirectoryIterator($flags = null)
    {
        if (isset($flags)) {
            $iterator = new \RecursiveDirectoryIterator($this->getRealPath(), $flags);
        } else {
            $iterator = new \RecursiveDirectoryIterator($this->getRealPath());
        }
        $iterator->setFileClass('Stalxed\FileSystem\FileObject');
        $iterator->setInfoClass('Stalxed\FileSystem\FileInfo');

        return $iterator;
    }
}
