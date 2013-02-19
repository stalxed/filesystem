<?php
namespace Stalxed\FileSystem\Control;

interface ControlInterface
{
    public function create($mode = 0644);
    public function delete();
    public function chmod($mode);
}
