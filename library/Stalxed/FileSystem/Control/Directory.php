<?php
namespace Stalxed\FileSystem\Control;

use Stalxed\FileSystem\FileInfo;

class Directory extends \SplFileInfo implements ControlInterface
{
    private $fileinfo;

    public function __construct(FileInfo $fileinfo)
    {
        $this->fileinfo = $fileinfo;
    }

    public function create($mode = 0755)
    {
        if ($this->fileinfo->isDir() || $this->fileinfo->isFile()) {
            throw new Exception\UnexpectedValueException();
        }

        if (! @mkdir($this->fileinfo->getRealPath(), $mode, true)) {
            throw new Exception\PermissionDeniedException();
        }
    }

    public function delete()
    {
        if (! $this->fileinfo->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }
        if (! $this->fileinfo->isEmpty()) {
            throw new Exception\DirectoryNotEmptyException();
        }

        if (! @rmdir($this->fileinfo->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }

    public function chmod($mode)
    {
        if (! $this->fileinfo->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }

        if (! @chmod($this->fileinfo->getRealPath(), $mode)) {
            throw new Exception\PermissionDeniedException();
        }
    }
}
