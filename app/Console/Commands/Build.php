<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class Build extends Command
{
    protected $signature = 'app:build {--version=}';

    public function handle()
    {
        $prefix = config('heptaconnect-shopware-six.app_name');
        $version = $this->option('version');

        if (!$version) {
            $tag = \trim((string) `git describe --tags --abbrev=0 --match='release/*'`);
            $version = \substr($tag, \strlen('release/'));
        }

        if (!$version) {
            $this->error('Release tag not found!');

            return 1;
        }

        $buildDisk = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/build'),
        ]);

        $resourcesDisk = Storage::build([
            'driver' => 'local',
            'root' => base_path('resources'),
        ]);

        $buildDisk->deleteDirectory($prefix);
        $buildDisk->put(
            $prefix . '/manifest.xml',
            View::make('integration.shopware-6.manifest-xml', ['version' => $version])->render()
        );
        $buildDisk->put(
            $prefix . '/icon.png',
            $resourcesDisk->get('img/shopware-app-logo-heptaconnect.png')
        );
    }
}
