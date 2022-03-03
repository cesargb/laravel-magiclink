<?php

namespace MagicLink\Actions;

use Illuminate\Support\Facades\Storage;

class DownloadFileAction extends ActionAbstract
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

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function disk(?string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function headers(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        return Storage::disk($this->disk)->download($this->path, $this->name, $this->headers);
    }
}
