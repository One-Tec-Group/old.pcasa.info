<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Common\Core\Controller;
use App\Services\ProjectRepository;
use Common\Settings\Settings;

class UserSiteController extends Controller
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Project $project
     * @param ProjectRepository $projectRepository
     * @param Settings $settings
     * @param Request $request
     */
    public function __construct(
        Project $project,
        ProjectRepository $projectRepository,
        Settings $settings,
        Request $request
    )
    {
        $this->project = $project;
        $this->projectRepository = $projectRepository;
        $this->settings = $settings;

        //user site routing is disabled by admin
        if ($this->settings->get('builder.routing_type') === 'none') abort(404);
        $this->request = $request;
    }

    /**
     * Show specified project's site.
     *
     * @param string $projectName
     * @param string|null $pageName
     * @param null $tls
     * @param null $page
     * @return string
     */
    public function show($projectName, $pageName = null, $tls = null, $page = null)
    {
        $project = $this->project->where('name', $projectName)->firstOrFail();

        //if it's subdomain routing, laravel will pass subdomain, domain, tls and then page name
        $pageName = $page ? $page : $pageName;

        $this->authorize('show', $project);

        try {
            $html = $this->projectRepository->getPageHtml($project, $pageName);
            return $this->replaceRelativeLinks($projectName, $html);
        } catch (FileNotFoundException $e) {
            return abort(404);
        }
    }

    /**
     * Replace relative urls in html to absolute ones.
     *
     * @param string $projectName
     * @param string $html
     * @return mixed
     */
    private function replaceRelativeLinks($projectName, $html)
    {
        preg_match_all('/<a.*?href="(.+?)"/i', $html, $matches);

        //there are no links in html
        if ( ! isset($matches[1])) return $html;

        if ($this->settings->get('builder.routing_type') === 'subdomain') {
            $base = $this->request->root();
        } else {
            $base = url("sites/$projectName");
        }

        //get rid of duplicate links
        $urls = array_unique($matches[1]);

        foreach ($urls as $url) {
            //if link is already absolute or an ID, continue to next one
            if (starts_with($url, ['//', 'http'])) continue;

            $searchUrl = str_replace('/', '\/', $url);
            $searchStr = "/href=\"$searchUrl\"/i";

            if (starts_with($url, '#')) {
                $html = preg_replace($searchStr, "href=\"{$base}{$url}\"", $html);
            } else {
                $html = preg_replace($searchStr, "href=\"$base/$url\"", $html);
            }
        }

        return $html;
    }
}
