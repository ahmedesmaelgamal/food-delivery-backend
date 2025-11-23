<?php

namespace App\Http\Controllers\Admin\Settings;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SoftwareUpdateRequest;
use App\Traits\ActivationClass;
use App\Traits\SettingsTrait;
use App\Traits\UpdateClass;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Mockery\Exception;
use ZipArchive;

class SoftwareUpdateController extends Controller
{
    use ActivationClass;
    use UpdateClass;
    use SettingsTrait;

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        return view('admin-views.system-setup.software-update');
    }

    public function update(SoftwareUpdateRequest $request): RedirectResponse|JsonResponse
    {
        $this->setEnvironmentValue(envKey: 'SOFTWARE_ID', envValue: 'MzE0NDg1OTc=');
        $this->setEnvironmentValue(envKey: 'BUYER_USERNAME', envValue: $request['username']);
        $this->setEnvironmentValue(envKey: 'PURCHASE_CODE', envValue: $request['purchase_key']);

        $data = $this->actch();
        try {
            if (!$data->getData()->active) {
                return redirect(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'));
            }
        } catch (Exception $exception) {
            ToastMagic::error(translate('verification_failed_try_again'));
            return back();
        }

        $file = $request->file('update_file');
        $fileName = 'update.' . $file->getClientOriginalExtension();
        $file->storeAs('uploads', $fileName);

        $status = true;
        $message = translate('software_updated_successfully');

        $execute = 0;
        $zip = new ZipArchive;
        if ($zip->open(base_path('storage/app/uploads/update.zip')) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                if (strpos($zip->getNameIndex($i), 'Library/Constant.php') && !strpos($zip->getNameIndex($i), '.env')) {
                    $matchesResult = [];
                    if (preg_match("/const\s+SOFTWARE_VERSION\s*=\s*'(\d+\.\d+)'/", $zip->getFromIndex($i), $matches)) {
                        $matchesResult = ['SOFTWARE_VERSION' => $matches[1]];
                    }
                    if (isset($matchesResult['SOFTWARE_VERSION']) && $matchesResult['SOFTWARE_VERSION'] > env('SOFTWARE_VERSION')) {
                        $execute = 1;
                    }
                }
            }
            $zip->close();
        }

        if ($execute) {
            $zip = new ZipArchive;
            if ($zip->open(base_path('storage/app/uploads/update.zip')) === TRUE) {
                $zip->open(base_path('storage/app/uploads/update.zip'));
                $zip->extractTo(base_path('.'));
                $zip->close();

                if (file_exists(base_path('app/Providers/RouteServiceProvider.txt'))) {
                    $previousRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.php');
                    $newRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.txt');
                    copy($newRouteServiceProvider, $previousRouteServiceProvider);
                }

                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('config:cache');
                Artisan::call('config:clear');

                $this->getProcessAllVersionsUpdates();
            }

            $this->setEnvironmentValue(envKey: 'SOFTWARE_VERSION', envValue: SOFTWARE_VERSION);
            $this->setEnvironmentValue(envKey: 'APP_MODE', envValue: 'live');
            $this->setEnvironmentValue(envKey: 'SESSION_LIFETIME', envValue: '60');
            ToastMagic::success($message);
        } else {
            $status = false;
            $message = translate('invalid_update_file');
            ToastMagic::error(translate('invalid_update_file'));
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => $status,
                'message' => $message
            ]);
        }

        return back();
    }
}
