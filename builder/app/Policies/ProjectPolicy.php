<?php

namespace App\Policies;

use App\User;
use App\Project;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function publish(User $user, Project $project)
    {
        return $user->hasPermission('projects.publish') && $this->show($user, $project);
    }

    public function download(User $user, Project $project)
    {
        return $user->hasPermission('projects.download') && $this->show($user, $project);
    }

    public function index(User $user, $userId)
    {
        return $user->id === (int) $userId || $user->hasPermission('projects.view');
    }

    public function show(User $user, Project $project)
    {
        return $project->published || $project->users->contains($user) || $user->hasPermission('projects.view');
    }

    public function store(User $user)
    {
        return $user->hasPermission('projects.create');
    }

    public function update(User $user, Project $project)
    {
        return $project->users->contains($user) || $user->hasPermission('projects.update');
    }

    public function destroy(User $user, Project $project)
    {
        return $project->users->contains($user) || $user->hasPermission('projects.delete');
    }
}
