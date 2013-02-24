<?php
namespace Stalxed\FileSystem;

use Stalxed\System\Random;

class FileObject extends \SplFileObject
{
    public function __construct($filename, $openMode = 'r', $useIncludePath = 'false', $context = null)
    {
        if (isset($context)) {
            parent::__construct($filename, $openMode, $useIncludePath, $context);
        } else {
            parent::__construct($filename, $openMode, $useIncludePath);
        }

        $this->setFileClass('Stalxed\FileSystem\FileObject');
        $this->setInfoClass('Stalxed\FileSystem\FileInfo');
    }

    public function isEmpty()
    {
        return ($this->getSize() == 0);
    }

    public function countLines()
    {
        $count = 0;
        foreach ($this as $temp) {
            ++$count;
        }

        $this->rewind();

        return $count;
    }

    public function getContents()
    {
        return file_get_contents($this->getRealPath());
    }

    public function getLines()
    {
        $lines = array();
        foreach ($this as $line) {
            $lines[] = $line;
        }

        $this->rewind();

        return $lines;
    }

    public function safeWrite($content)
    {
        if ($this->flock(LOCK_EX)) {
            $this->fwrite($content);
            $this->flock(LOCK_UN);
        }
    }

    public function findLineByNumber($line_number)
    {
        $desired_line = null;

        $i = 0;
        foreach ($this as $line) {
            if ($i == $line_number) {
                $desired_line = $line;

                break;
            }

            ++$i;
        }

        $this->rewind();

        return $desired_line;
    }

    public function findLineByString($desired_value, $desired_start_position = null)
    {
        $desired_line = null;

        foreach ($this as $line) {
            $position = strpos($line, $desired_value);
            if ($position !== false) {
                if ($desired_start_position === null || $position == $desired_start_position) {
                    $desired_line = $line;

                    break;
                }
            }
        }

        $this->rewind();

        return $desired_line;
    }

    public function findRandomLine()
    {
        $desired_line = null;

        $random = new Random();

        $i = 0;
        foreach ($this as $line) {
            if ($random->generateNumber(0, $i) < 1) {
                $desired_line = $line;
            }

            ++$i;
        }

        $this->rewind();

        return $desired_line;
    }

    public function copyTo($pathDestinationFile, $copyMode = CopyMode::SKIP_EXISTING, $mode = 0644)
    {
        $destinationFile = new FileInfo($pathDestinationFile);
        if (! $destinationFile->isExists()) {
            if (! @copy($this->getRealPath(), $destinationFile->getPathname())) {
                throw new Exception\PermissionDeniedException();
            }
            if (! @chmod($destinationFile->getRealPath(), $mode)) {
                throw new Exception\PermissionDeniedException();;
            }
        } elseif ($copyMode == CopyMode::ABORT_IF_EXISTS) {
            throw new Exception\AbortException();
        }
    }
}
