<?php
namespace Stalxed\FileSystem;

class Symlink extends \SplFileInfo
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

    public function getSize()
    {
        return 0;
    }

    public function isEmpty()
    {
        return (! $this->isDir() && ! $this->isFile());
    }

    public function copyTo($pathDestinationFile, $copyMode = CopyMode::SKIP_EXISTING, $mode = 0644)
    {

    }
}
