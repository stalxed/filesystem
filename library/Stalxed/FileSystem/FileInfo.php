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
     * Создаёт файл.
     * По умолчанию устанавливает права доступа 0644.
     *
     * @param integer $mode
     * @throws System_FSException
     */
    public function createFile($mode = 0644)
    {
        if (!@touch($this->path)) {
            throw new Exception\RuntimeException('Failed to create file.', $this->path);
        }

        if (!@chmod($this->path, $mode)) {
            throw new Exception\RuntimeException('Failed to change permissions.', $this->path);
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
        if (!@mkdir($this->directory_path, $mode, true)) {
            throw new Exception\RuntimeException('Failed to create directory.', $this->directory_path);
        }
    }

    /**
     * Удаляет файл.
     *
     * @throws System_FSException
     */
    public function deleteFile()
    {
        if (!@unlink($this->path)) {
            throw new Exception\RuntimeException('Failed to delete file.', $this->path);
        }
    }

    /**
     * Удаляет директорию.
     *
     * @throws System_FSException
     */
    public function deleteDirectory()
    {
        if (!@rmdir($this->directory_path)) {
            throw new Exception\RuntimeException('Failed to delete directory.', $this->directory_path);
        }
    }
}
