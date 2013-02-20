<?php
namespace Stalxed\FileSystem\Control;

use Stalxed\FileSystem\FileInfo;

class Link
{
    private $fileinfo;

    public function __construct(FileInfo $fileinfo)
    {
        $this->fileinfo = $fileinfo;
    }

    public function delete()
    {
        if (! $this->fileinfo->isLink()) {
            throw new Exception\FileNotFoundException();
        }

        if (! @unlink($this->fileinfo->getRealPath())) {
            throw new Exception\PermissionDeniedException();
        }
    }
}
?>