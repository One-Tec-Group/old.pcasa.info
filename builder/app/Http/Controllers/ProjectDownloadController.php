<?php

namespace App\Http\Controllers;

use Zipper;
use Common\Core\Controller;
use App\Services\ProjectRepository;

class ProjectDownloadController extends Controller
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * ProjectDownloadController constructor.
     *
     * @param ProjectRepository $projectRepository
     */
    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * Download specified project as a .zip
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function download($id)
    {
        $project = $this->projectRepository->findOrFail($id);

        $this->authorize('download', $project);

        $source = config('filesystems.disks.public.root').'/'.$this->projectRepository->getProjectPath($project);
        $destination = "$source/$project->name.zip";

        //delete old project zip file
        if (file_exists($destination)) {
            unlink($destination);
        }

        //create a zip
        $files = glob($source);
        Zipper::make($destination)->add($files)->close();

        //download
        return response()->download($destination);
    }
}
