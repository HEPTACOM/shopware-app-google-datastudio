<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DownloadShopwareAdministrationStyle extends Command
{
    protected $signature = 'app:download-shopware-administration-style';

    public function handle()
    {
        $gitDir = \tempnam(\sys_get_temp_dir(), 'hccloud-git') . '.d';
        \mkdir($gitDir);

        $this->cloneRepository($gitDir);
        $tags = $this->listTags($gitDir);

        foreach ($tags as $tag) {
            $tarFile = \tempnam(\sys_get_temp_dir(), 'hccloud-git-tar');

            if ($this->tarTag($gitDir, $tarFile, $tag)) {
                $outDir = \storage_path('app/public/shopware-admin/' . $tag);
                $tagDir = \tempnam(\sys_get_temp_dir(), 'hccloud-git-tag') . '.d';

                if (!\is_dir($outDir)) {
                    \mkdir($outDir, 0777, true);
                }

                if (!\is_dir($tagDir)) {
                    \mkdir($tagDir);
                }

                $this->untar($tarFile, $tagDir);
                $this->mv($tagDir . '/Resources/public/static', $outDir);
            }
        }
    }

    protected function cloneRepository(string $directory): void
    {
        $process = new Process(['git', 'clone', 'https://github.com/shopware/administration', $directory]);
        $process->start();
        $process->wait();
    }

    protected function tarTag(string $directory, string $outputFile, string $tag): bool
    {
        $process = new Process(['git', 'archive', '-o', $outputFile, $tag, 'Resources/public/static'], $directory);
        $process->start();
        $process->wait();

        return ($process->getExitCode() ?? -1) === 0;
    }

    protected function untar(string $file, string $outputDirectory): void
    {
        $process = new Process(['tar', '-xf', $file], $outputDirectory);
        $process->start();
        $process->wait();
    }

    /**
     * @see https://www.php.net/manual/en/function.rename.php#117590
     */
    protected function mv(string $from, string $to): void
    {
        $process = new Process(['mv', $from, $to]);
        $process->start();
        $process->wait();
    }

    protected function listTags(string $directory): array
    {
        $process = new Process(['git', 'tag', '--list'], $directory);
        $process->enableOutput();
        $process->start();
        $process->wait();

        return \array_filter(\array_map('trim', \preg_split("/[\n\r]/", $process->getOutput())));
    }
}
