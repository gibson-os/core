<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class FfmpegInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): Generator
    {
        yield $ffmpegPathInput = $this->getEnvInput('FFMPEG_PATH', 'What is the ffmpeg path?');
        yield $ffprobePathInput = $this->getEnvInput('FFPROBE_PATH', 'What is the ffprobe path?');

        yield (new Configuration('FFMPEG configuration generated!'))
            ->setValue('FFMPEG_PATH', $ffmpegPathInput->getValue() ?? '')
            ->setValue('FFPROBE_PATH', $ffprobePathInput->getValue() ?? '')
        ;
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getPriority(): int
    {
        return 800;
    }
}
