<?php

namespace MagicLink\Actions;

use MagicLink\Actions\Concerns\HandlesStorageFile;

class InlineFileAction extends ActionAbstract
{
    use HandlesStorageFile;

    /**
     * Execute Action.
     */
    public function run()
    {
        return $this->getDisk()->response($this->path, $this->name, $this->headers);
    }
}
