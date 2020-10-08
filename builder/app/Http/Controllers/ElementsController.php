<?php namespace App\Http\Controllers;

use Common\Core\Controller;
use Illuminate\Filesystem\Filesystem;

class ElementsController extends Controller
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * ElementsController constructor.
     *
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Parse and compile all custom elements in elements folder.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function custom()
    {
        $elements = array();

        $files = $this->fs->files(public_path('builder/elements'));

        foreach ($files as $file) {
            $contents = $this->fs->get($file);

            preg_match('/<script>(.+?)<\/script>/s', $contents, $config);
            preg_match('/<style.*?>(.+?)<\/style>/s', $contents, $css);
            preg_match('/<\/style.*?>(.+?)<script>/s', $contents, $html);

            if ( ! isset($config[1]) || ! isset($html[1])) {
                continue;
            }

            $elements[] = array(
                'css' => isset($css[1]) ? trim($css[1]) : '',
                'html' => trim($html[1]),
                'config' => trim($config[1])
            );
        }

        return response()->json($elements);
    }
}