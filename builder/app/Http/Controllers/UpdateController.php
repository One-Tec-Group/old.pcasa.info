<?php namespace App\Http\Controllers;

use Auth;
use App\User;
use Cache;
use Artisan;
use Common\Auth\Roles\Role;
use Exception;
use Common\Settings\Setting;
use Common\Settings\DotEnvEditor;
use Common\Core\Controller;
use Illuminate\Support\Collection;

class UpdateController extends Controller {
    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    /**
     * @var Setting
     */
    private $setting;

    /**
     * @var User
     */
    private $user;

    /**
     * UpdateController constructor.
     *
     * @param DotEnvEditor $dotEnvEditor
     * @param Setting $setting
     * @param User $user
     */
	public function __construct(DotEnvEditor $dotEnvEditor, Setting $setting, User $user)
	{
        $this->user = $user;
        $this->setting = $setting;
        $this->dotEnvEditor = $dotEnvEditor;

        if ( ! config('common.site.disable_update_auth') && version_compare(config('common.site.version'), $this->getAppVersion()) === 0) {
            if ( ! Auth::check() || ! Auth::user()->hasPermission('admin')) {
                abort(403);
            }
        }
    }

    /**
     * Show update view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        return view('update');
    }

    /**
     * Perform the update.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
	{
	    //fix "index is too long" issue on MariaDB and older mysql versions
        \Schema::defaultStringLength(191);

        Artisan::call('migrate', ['--force' => 'true']);
        Artisan::call('db:seed', ['--force' => 'true']);
        Artisan::call('common:seed');

        if (version_compare(config('common.site.version'), '2.1.0') === -1) {
            //move translations and custom code from database to filesystem
            Artisan::call('translations:migrate_from_database');
            Artisan::call('custom_code:migrate_from_database');

            // update date format
            $this->setting->where('name', 'dates.format')->update(['value' => 'yyyy-MM-dd']);

            //rename "uploads" permission to "files"
            Role::orderBy('id')->chunk(50, function(Collection $roles) {
                $roles->each(function(Role $role) {
                    $oldPermissions = json_encode($role->permissions);
                    $newPermissions = str_replace('uploads', 'files', $oldPermissions);
                    $role->permissions = $newPermissions;
                    $role->save();
                });
            });
        }

        //migrate versions prior to 2.0
        if (version_compare(config('common.site.version'), '2.0.0', '<=')) {
            $this->user->where('permissions', '{"superuser":1}')->update(['permissions' => '{"superuser":1, "admin": 1}']);
            Artisan::call('legacy:projects');
        }

        //update version number
        $version = $this->getAppVersion();
        $this->dotEnvEditor->write(['app_version' => $version]);

        Cache::flush();

        return redirect()->back()->with('status', 'Updated the site successfully.');
	}


    /**
     * Get new app version.
     *
     * @return string
     */
    private function getAppVersion()
    {
        try {
            return $this->dotEnvEditor->load(base_path('.env.example'))['app_version'];
        } catch (Exception $e) {
            return '2.1.4';
        }
    }
}