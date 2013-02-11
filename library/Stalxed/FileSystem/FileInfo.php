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
            return $this->openDirectory()->getSize();
        }
        if ($this->isFile()) {
            return $this->openFile()->getSize();
        }

        throw new Exception\PathNotFoundException();
    }

    public function isEmpty()
    {
        if ($this->isDir()) {
            return $this->openDirectory()->isEmpty();
        }
        if ($this->isFile()) {
            return $this->openFile()->isEmpty();
        }

        throw new Exception\PathNotFoundException();
    }

    public function openDirectory()
    {
        return new DirectoryObject($this->getRealPath());
    }

    public function controlDirectory()
    {
        return new Control\Directory($this->getRealPath());
    }

    public function conrolFile()
    {
        return new Control\File($this->getRealPath());
    }
}
