<?php

namespace MagicLink\Actions;

use Illuminate\Support\Facades\Storage;

class DownloadFileAction implements ActionInterface
{
    protected $path;

    protected $name;

    protected $headers;

    /**
     * Constructor to action.
     *
     * @param  string  $path
     * @param  string|null  $name
     * @param  array  $headers
     * @return void
     */
    public function __construct(string $path, ?string $name = null, array $headers = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->headers = $headers;
    }

    /**
     * Execute Action.
     */
    public function run()
    {
        return Storage::download($this->path, $this->name, $this->headers);
    }
}
