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

    public function isExists()
    {
        return ($this->isFile() || $this->isDir() || $this->isLink());
    }

    public function getSize()
    {
        if ($this->isLink()) {
            return $this->openSymlink()->getSize();
        }
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
        if ($this->isLink()) {
            return $this->openSymlink()->isEmpty();
        }
        if ($this->isDir()) {
            return $this->openDirectory()->isEmpty();
        }
        if ($this->isFile()) {
            return $this->openFile()->isEmpty();
        }

        throw new Exception\PathNotFoundException();
    }

    public function openSymlink()
    {
        return new Symlink($this->getPathname());
    }

    public function openDirectory()
    {
        return new DirectoryObject($this->getRealPath());
    }

    public function openFile($openMode = 'r', $useIncludePath = 'false')
    {
        return new FileObject($this->getRealPath(), $openMode, $useIncludePath);
    }

    public function control($type = null)
    {
        if ($this->isLink()) {
            return new Control\Symlink($this);
        }
        if ($this->isDir()) {
            return new Control\Directory($this);
        }
        if ($this->isFile()) {
            return new Control\File($this);
        }

        throw new Exception\PathNotFoundException();
    }

    public function controlAsSymlink()
    {
        return new Control\Symlink($this);
    }

    public function controlAsDirectory()
    {
        return new Control\Directory($this);
    }

    public function controlAsFile()
    {
        return new Control\File($this);
    }
}
