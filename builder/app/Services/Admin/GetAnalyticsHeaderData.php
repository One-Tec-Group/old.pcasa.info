<?php

namespace App\Services\Admin;

use App\BuilderPage;
use App\Project;
use App\Services\TemplateLoader;
use App\User;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;

class GetAnalyticsHeaderData implements GetAnalyticsHeaderDataAction
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var BuilderPage
     */
    private $page;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var TemplateLoader
     */
    private $templateLoader;

    /**
     * @param BuilderPage $page
     * @param User $user
     * @param Project $project
     * @param TemplateLoader $templateLoader
     */
    public function __construct(BuilderPage $page, User $user, Project $project, TemplateLoader $templateLoader)
    {
        $this->page = $page;
        $this->user = $user;
        $this->project = $project;
        $this->templateLoader = $templateLoader;
    }

    public function execute()
    {
        return [
            [
                'icon' => 'people',
                'name' => 'Total Users',
                'type' => 'number',
                'value' => number_format($this->user->count()),
            ],
            [
                'icon' => 'dashboard',
                'name' => 'Total Projects',
                'type' => 'number',
                'value' => number_format($this->project->count()),
            ],
            [
                'icon' => 'content-copy',
                'name' => 'Total Pages',
                'type' => 'number',
                'value' => number_format($this->page->count() || 1),
            ],
            [
                'icon' => 'web-design-custom',
                'name' => 'Total Templates',
                'type' => 'number',
                'value' => number_format($this->templateLoader->loadAll()->count()),
            ],
        ];
    }
}