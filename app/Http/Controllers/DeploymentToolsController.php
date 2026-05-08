<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DeploymentToolsController extends Controller
{
    public function index(): View
    {
        $publicStoragePath = public_path('storage');
        $storagePublicPath = storage_path('app/public');
        $isSymlink = is_link($publicStoragePath);
        $publicStorageExists = File::exists($publicStoragePath);
        $publicStorageIsDirectory = File::isDirectory($publicStoragePath);
        $storageStatus = $this->resolveStorageStatus($isSymlink, $publicStorageExists, $publicStorageIsDirectory, $publicStoragePath, $storagePublicPath);
        $buildManifestPath = public_path('build/manifest.json');
        $logPath = storage_path('logs/laravel.log');

        return view('deployment-tools.index', [
            'checks' => [
                'storage_status' => $storageStatus,
                'storage_link' => in_array($storageStatus, ['linked-symlink', 'usable-directory'], true),
                'public_storage_exists' => $publicStorageExists,
                'vite_manifest' => File::exists($buildManifestPath),
                'filament_vendor_assets' => $this->filamentAssetsExist(),
                'log_exists' => File::exists($logPath),
            ],
            'paths' => [
                'public_storage' => $publicStoragePath,
                'storage_public' => $storagePublicPath,
                'build_manifest' => $buildManifestPath,
                'log' => $logPath,
            ],
            'app' => [
                'env' => config('app.env'),
                'url' => config('app.url'),
                'asset_url' => config('app.asset_url'),
                'debug' => (bool) config('app.debug'),
            ],
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'string'],
        ]);

        $action = (string) $request->string('action');
        $result = match ($action) {
            'storage-link' => $this->runStorageLink(),
            'clear-caches' => $this->runCommand('optimize:clear'),
            'filament-upgrade' => $this->runCommand('filament:upgrade'),
            default => ['ok' => false, 'message' => 'Unknown action.', 'output' => 'No command was executed.'],
        };

        return back()
            ->with('status', $result['message'])
            ->with('deployment_result', $result);
    }

    private function runStorageLink(): array
    {
        $publicStorage = public_path('storage');
        $storagePublic = storage_path('app/public');

        if ($this->pathsMatch($publicStorage, $storagePublic) || is_link($publicStorage)) {
            return [
                'ok' => true,
                'message' => 'storage:link already configured.',
                'output' => 'Storage path is already linked.',
            ];
        }

        try {
            Artisan::call('storage:link');
            $output = $this->normalizeOutput(trim(Artisan::output()), 'storage:link completed with no console output.');

            return ['ok' => true, 'message' => 'storage:link executed successfully.', 'output' => $output];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => 'storage:link failed.',
                'output' => $exception->getMessage(),
            ];
        }
    }

    private function runCommand(string $command): array
    {
        try {
            Artisan::call($command);
            $output = $this->normalizeOutput(trim(Artisan::output()), $command.' completed with no console output.');

            return ['ok' => true, 'message' => $command.' executed successfully.', 'output' => $output];
        } catch (\Throwable $exception) {
            return [
                'ok' => false,
                'message' => $command.' failed.',
                'output' => $exception->getMessage(),
            ];
        }
    }

    private function pathsMatch(string $pathA, string $pathB): bool
    {
        $realA = realpath($pathA);
        $realB = realpath($pathB);

        return $realA !== false && $realB !== false && $realA === $realB;
    }

    private function resolveStorageStatus(
        bool $isSymlink,
        bool $publicStorageExists,
        bool $publicStorageIsDirectory,
        string $publicStoragePath,
        string $storagePublicPath,
    ): string {
        if ($isSymlink || $this->pathsMatch($publicStoragePath, $storagePublicPath)) {
            return 'linked-symlink';
        }

        if ($publicStorageExists && $publicStorageIsDirectory) {
            return 'usable-directory';
        }

        return 'not-linked';
    }

    private function filamentAssetsExist(): bool
    {
        return File::isDirectory(public_path('vendor/filament'))
            || File::exists(public_path('js/filament/filament/app.js'))
            || File::exists(public_path('css/filament/filament/app.css'));
    }

    private function normalizeOutput(string $output, string $fallback): string
    {
        if ($output === '') {
            return $fallback;
        }

        return $output;
    }
}
