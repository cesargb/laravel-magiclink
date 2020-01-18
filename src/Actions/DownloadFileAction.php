<?php

namespace Cesargb\MagicLink\Actions;

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
     * @param  array|null  $headers
     * @return void
     */
    public function __construct($path, $name = null, $headers = [])
    {
        $this->path = $path;
        $this->name = $name;
        $this->headers = $headers;
    }

    /**
     * Execute Action.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function run()
    {
        return Storage::download($this->path, $this->name, $this->headers);
    }
}
