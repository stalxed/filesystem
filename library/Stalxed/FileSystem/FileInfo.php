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
        if (parse_url($this->getPathname(), PHP_URL_SCHEME) == 'vfs') {
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

    public function control()
    {
        if ($this->isDir()) {
            return new Control\Directory($this);
        }
        if ($this->isFile()) {
            return new Control\File($this);
        }

        throw new Exception\PathNotFoundException();
    }
}
