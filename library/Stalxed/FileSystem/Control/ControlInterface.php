<?php
namespace Stalxed\FileSystem\Control;

interface ControlInterface
{
    public function create($mode);
    public function delete();
}
