<?php
namespace Stalxed\FileSystem;

class CopyMode
{
    const SKIP_EXISTING = 0;
    const OVERWRITE_EXISTING = 1;
    const ABORT_IF_EXISTS = 2;

    final private function __construct ()
    {}
}
