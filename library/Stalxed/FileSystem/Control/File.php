<?php
namespace Stalxed\FileSystem\Control;

use Stalxed\FileSystem\FileInfo;

class File implements ControlInterface
{
    private $fileinfo;

    public function __construct(FileInfo $fileinfo)
    {
        $this->fileinfo = $fileinfo;
    }

    public function create($mode = 0644)
    {
        if (! $this->fileinfo->getPathInfo()->isDir()) {
            throw new Exception\DirectoryNotFoundException();
        }
        if ($this->fileinfo->isExists()) {
            throw new Exception\UnexpectedValueException();
        }

        if (! @touch($this->fileinfo->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
        $this->chmod($mode);
    }

    public function delete()
    {
        if (! $this->fileinfo->isFile()) {
            throw new Exception\FileNotFoundException();
        }

        if (! @unlink($this->fileinfo->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }

    public function chmod($chmod)
    {
        if (! $this->fileinfo->isFile()) {
            throw new Exception\FileNotFoundException();
        }

        if (! @chmod($this->fileinfo->getRealPath(), $chmod)) {
            throw new Exception\PermissionDeniedException();
        }
    }
}
