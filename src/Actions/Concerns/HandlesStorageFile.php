<?php

namespace MagicLink\Actions\Concerns;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

trait HandlesStorageFile
{
    protected $path;

    protected $name;

    protected $headers;

    protected $disk;

    /**
     * Constructor to action.
     *
     * @return void
     */
    public function __construct(string $path, ?string $name = null, array $headers = [])
    {
        $this->path = $path;

        if (! is_null($name)) {
            $this->name($name);
        }

        $this->headers($headers);
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function disk(?string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    private function getDisk(): FilesystemAdapter
    {
        return Storage::disk($this->disk);
    }
}
