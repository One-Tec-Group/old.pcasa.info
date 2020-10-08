<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ThemesLoader;
use Common\Core\Controller;

class ThemesController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ThemesLoader
     */
    private $loader;

    /**
     * Create new ThemesController instance.
     *
     * @param Request $request
     * @param ThemesLoader $loader
     */
	public function __construct(Request $request, ThemesLoader $loader)
	{
		$this->request = $request;
        $this->loader = $loader;
    }

    /**
     * Return all available templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
	public function index()
	{
	    $themes = $this->loader->loadAll();

	    return $this->success(['themes' => $themes]);
	}
}