<?php
namespace Stalxed\FileSystem\Control;

class Directory extends \SplFileInfo implements ControlInterface
{
    public function getRealPath()
    {
        if (in_array('vfs', stream_get_wrappers())) {
            return $this->getPathname();
        }

        return parent::getRealPath();
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
        if (! @mkdir($this->getRealPath(), $mode, true)) {
            throw new Exception\PermissionDeniedException();
        }
    }

    /**
     * Удаляет директорию.
     *
     * @throws System_FSException
     */
    public function delete()
    {
        if ($this->isFile()) {
            throw new Exception\LogicException();
        }
        if (! $this->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        if (! @rmdir($this->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }
}
