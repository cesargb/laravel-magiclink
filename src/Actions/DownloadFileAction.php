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
        $this->name = $name;
        $this->headers = $headers;
    }

    public function disk(?string $disk): self
    {
        $this->disk = $disk;

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
