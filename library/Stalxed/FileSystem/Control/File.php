<?php
namespace Stalxed\FileSystem\Control;

class File extends \SplFileInfo implements ControlInterface
{
    public function getRealPath()
    {
        if (in_array('vfs', stream_get_wrappers())) {
            return $this->getPathname();
        }

        return parent::getRealPath();
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
     * Удаляет файл.
     *
     * @throws System_FSException
     */
    public function delete()
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
}
