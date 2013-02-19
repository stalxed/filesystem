<?php
namespace Stalxed\FileSystem;

class DirectoryObject extends \SplFileInfo
{
    public function __construct($filename)
    {
        parent::__construct($filename);

        if (! $this->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        $this->setFileClass('Stalxed\FileSystem\FileObject');
        $this->setInfoClass('Stalxed\FileSystem\FileInfo');
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
        $size = 0;
        foreach ($this->createDirectoryIterator() as $item) {
            $size += $item->getFileInfo()->getSize();
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
        foreach ($this->createDirectoryIterator() as $item) {
            return false;
        }

        return true;
    }

    /**
     * Recursively delete subdirectories and files.
     *
     * @throws System_FSException
     */
    public function clear()
    {
        foreach ($this->createDirectoryIterator() as $item) {
            $fileinfo = $item->getFileInfo();
            if ($fileinfo->isDir()) {
                $fileinfo->openDirectory()->clear();
            }
            $fileinfo->control()->delete();
        }
    }

    public function chmodInternalContent($mode)
    {
        foreach ($this->createDirectoryIterator() as $item) {
            $fileinfo = $item->getFileInfo();
            if ($fileinfo->isDir()) {
                $fileinfo->openDirectory()->chmodInternalContent($mode);
            }
            $fileinfo->control()->chmod($mode);
        }
    }

    /**
     * Копирует директорию со всеми вложенными элементами.
     * Если файл назначения уже существует, то его копирование
     * не выполняет. По умолчанию устанавливает права доступа
     * 0755 для директорий и 0644 для файлов.
     *
     * @param string $pathDestinationDirectory
     * @param integer $dirmode
     * @param integer $filemode
     * @throws System_FSException
     */
    public function copyTo(
        $pathDestinationDirectory,
        $copyMode = CopyMode::SKIP_EXISTING,
        $dirmode = 0755,
        $filemode = 0644
    ) {
        $destinationDirectory = new FileInfo($pathDestinationDirectory);
        if (! $destinationDirectory->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        $iterator = $this->createRecursiveDirectoryIterator();
        $iterator->rewind();

        $newDestinationDirectory = new FileInfo($destinationDirectory->getRealPath() . '/' . $this->getBasename());
        if ( ! $newDestinationDirectory->isDir()) {
            $newDestinationDirectory->control()->create();
        } elseif ($copyMode == CopyMode::ABORT_IF_EXISTS) {
            throw new Exception\AbortException();
        }

        $this->coping($iterator, $newDestinationDirectory->getRealPath(), $dirmode, $filemode);
    }

    /**
     * Рекурсивная функция для копирования директории со всеми
     * вложенными элементами.
     *
     * @param RecursiveDirectoryIterator $iterator
     * @param string $pathDestinationDirectory
     * @param integer $dirmode
     * @param integer $filemode
     * @throws System_FSException
     */
    private function coping(
        \RecursiveDirectoryIterator $iterator,
        $pathDestinationDirectory,
        $copyMode = CopyMode::SKIP_EXISTING,
        $dirmode = 0755,
        $filemode = 0644
    ) {
        foreach ($iterator as $item) {
            $fileinfo = $item->getFileInfo();
            $destinationDirectory = new FileInfo($pathDestinationDirectory . '/' . $fileinfo->getBasename());

            if ($iterator->hasChildren()) {
                if (! $destinationDirectory->isDir()) {
                    $destinationDirectory->controlDirectory()->create($dirmode);
                } elseif($copyMode == CopyMode::ABORT_IF_EXISTS) {
                    throw new Exception\AbortException();
                }

                $this->coping($iterator->getChildren(), $destinationDirectory->getRealPath(), $dirmode, $filemode);
            } else {
                $fileinfo->openFile()->copyTo($destinationDirectory->getRealPath(), $copyMode);
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
            $iterator = new \FilesystemIterator($this->getRealPath(), $flags);
        } else {
            $iterator = new \FilesystemIterator($this->getRealPath());
        }
        $iterator->setFileClass('Stalxed\FileSystem\FileObject');
        $iterator->setInfoClass('Stalxed\FileSystem\FileInfo');

        return $iterator;
    }

    public function createGlobIterator($flags)
    {
        if (isset($flags)) {
            $iterator = new \GlobIterator($this->getRealPath(), $flags);
        } else {
            $iterator = new \GlobIterator($this->getRealPath());
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
