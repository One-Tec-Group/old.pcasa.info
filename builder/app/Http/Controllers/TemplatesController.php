<?php namespace App\Http\Controllers;

use App\Services\TemplateRepository;
use Illuminate\Http\JsonResponse;
use Zipper;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Common\Core\Controller;
use App\Services\TemplateLoader;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class TemplatesController extends Controller {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var TemplateLoader
     */
    private $templateLoader;

    /**
     * @var TemplateRepository
     */
    private $repository;

    /**
     * @param Request $request
     * @param TemplateLoader $templateLoader
     * @param TemplateRepository $repository
     */
	public function __construct(Request $request, TemplateLoader $templateLoader, TemplateRepository $repository)
	{
		$this->request = $request;
        $this->repository = $repository;
        $this->templateLoader = $templateLoader;
    }

    /**
     * Return all available templates.
     *
     * @return JsonResponse
     */
	public function index()
	{
	    $this->authorize('index', 'Template');

	    $templates = $this->templateLoader->loadAll();

	    $perPage = $this->request->get('per_page', 10);
	    $page = $this->request->get('page', 1);

	    if ($this->request->has('query')) {
	        $templates = $templates->filter(function($template) {
	           return str_contains(strtolower($template['name']), $this->request->get('query'));
            });
        }

        if ($orderBy = $this->request->get('order_by', 'updated_at')) {
            $desc = $this->request->get('order_dir', 'desc') === 'desc';
            $templates = $templates->sortBy($orderBy, SORT_REGULAR, $desc);
        }

	    $pagination = new LengthAwarePaginator(
	        $templates->slice($perPage * ($page - 1), $perPage)->values(),
            count($templates),
            $perPage,
            $page
        );

	    return $this->success(['pagination' => $pagination]);
	}

    /**
     * Get template by specified name.
     *
     * @param string $name
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($name)
    {
        $this->authorize('show', 'Template');

        try {
            $template = $this->templateLoader->load($name);
        } catch (FileNotFoundException $exception) {
            return abort(404);
        }

        return $this->success(['template' => $template]);
    }

    /**
     * Create a new template.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws FileNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store()
    {
        $this->authorize('store', 'Template');

        $this->validate($this->request, [
            'display_name' => 'required|string|min:1|max:255',
            'category' => 'required|string|min:1|max:255',
            'template' => 'required|file|mimes:zip',
            'thumbnail' => 'required|file|image'
        ]);

        $params = $this->request->except('template');
        $params['template'] = $this->request->file('template');
        $params['thumbnail'] = $this->request->file('thumbnail');

        if ($this->templateLoader->exists($params['display_name'])) {
            return $this->error(['display_name' => 'Template with this name already exists.']);
        }

        $this->repository->create($params);

        return $this->success([
            'template' => $this->templateLoader->load($params['display_name'])
        ]);
    }

    /**
     * Update existing template.
     *
     * @param string $name
     * @return \Illuminate\Http\JsonResponse
     * @throws FileNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($name)
    {
        $this->authorize('update', 'Template');

        $this->validate($this->request, [
            'display_name' => 'string|min:1|max:255',
            'category' => 'string|min:1|max:255',
            'template' => 'file|mimes:zip',
            'thumbnail' => 'file|image'
        ]);

        $params = $this->request->except('template');
        $params['template'] = $this->request->file('template');
        $params['thumbnail'] = $this->request->file('thumbnail');

        $this->repository->update($name, $params);

        return $this->success(['template' => $this->templateLoader->load($name)]);
    }

    /**
     * Delete specified templates.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy()
    {
        $this->authorize('destroy', 'Template');

        $this->repository->delete($this->request->get('names'));

        return $this->success();
    }
}