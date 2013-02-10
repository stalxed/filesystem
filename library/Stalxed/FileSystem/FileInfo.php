<?php
namespace Stalxed\FileSystem;

class FileInfo extends \SplFileInfo
{
    public function __construct($filename)
    {
        $this->setFileClass('Stalxed\FileSystem\FileObject');
        $this->setInfoClass('Stalxed\FileSystem\FileInfo');

        parent::__construct($filename);
    }

    /**
     * (non-PHPdoc)
     *
     * @see SplFileInfo::getRealPath()
     */
    public function getRealPath()
    {
        if (in_array('vfs', stream_get_wrappers())) {
            return $this->getPathname();
        }

        return parent::getRealPath();
    }

    public function getSize()
    {
        if ($this->isDir()) {
            $this->openDirectory()->getSize();
        }
        if ($this->isFile()) {
            $this->openFile()->getSize();
        }
    }

    public function isEmpty()
    {
        if ($this->isDir()) {
            $this->openDirectory()->isEmpty();
        }
        if ($this->isFile()) {
            $this->openFile()->isEmpty();
        }
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
    public function createDirectory($mode = 0755)
    {
        if (! @mkdir($this->getRealPath(), $mode, true)) {
            throw new Exception\PermissionDeniedException();
        }
    }

    /**
     * Создаёт файл.
     * По умолчанию устанавливает права доступа 0644.
     *
     * @param integer $mode
     * @throws System_FSException
     */
    public function createFile($mode = 0644)
    {
        if (! $this->getPathInfo()->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        if (! @touch($this->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
        if (! @chmod($this->getRealPath(), $mode)) {
            throw new Exception\PermissionDeniedException();
        }
    }

    /**
     * Удаляет директорию.
     *
     * @throws System_FSException
     */
    public function deleteDirectory()
    {
        if ($this->isFile()) {
            throw new Exception\LogicException;
        }
        if (! $this->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        if (! @rmdir($this->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }

    /**
     * Удаляет файл.
     *
     * @throws System_FSException
     */
    public function deleteFile()
    {
        if ($this->isDir()) {
            throw new Exception\LogicException();
        }
        if (! $this->isFile()) {
            throw new Exception\FileNotFoundException();
        }

        if (! @unlink($this->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }

    public function openDirectory()
    {
        if ($this->isFile()) {
            throw new Exception\LogicException();
        }

        return new DirectoryObject($this->getRealPath());
    }
}
