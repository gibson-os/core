<?php
declare(strict_types=1);

namespace GibsonOS\Core\Command;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Service\ProcessService;
use Psr\Log\LoggerInterface;

/**
 * @description Collect SMART information of all hard drives
 */
#[Cronjob(minutes: '0,15,30,45', seconds: '0')]
class DriveStatCommand extends AbstractCommand
{
    public function __construct(private readonly ProcessService $processService, LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function run(): int
    {
        foreach (glob('/dev/sd?') ?: [] as $disk) {
            $this->processService->execute('/sbin/hdparm -i ' . $disk . ' > /tmp/hdparm.tmp');
        }

        $hdParm = file('/tmp/hdparm.tmp');

        foreach ($hdParm ?: [] as $row) {
            if (mb_strpos($row, '=') === false) {
                continue;
            }

            $propertyList = explode(', ', $row);

            foreach ($propertyList as $property) {
                $keyValue = explode('=', $property);

                if (count($keyValue) % 2 != 0) {
                    continue;
                }

                //                switch (mb_strtolower(trim($keyValue[0]))) {
                //                    case 'serialno':
                //                        $System->system_driveTable->serial->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'fwrev':
                //                        $System->system_driveTable->fw_rev->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'rawchs':
                //                        $System->system_driveTable->raw_chs->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'trksize':
                //                        $System->system_driveTable->track_size->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'sectsize':
                //                        $System->system_driveTable->sect_size->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'eccbytes':
                //                        $System->system_driveTable->ecc_bytes->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'bufftype':
                //                        $System->system_driveTable->buff_type->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'buffsize':
                //                        $System->system_driveTable->buff_size->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'maxmultsect':
                //                        $System->system_driveTable->max_mult_sect->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'multsect':
                //                        $System->system_driveTable->mult_sect->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'curchs':
                //                        $System->system_driveTable->cur_chs->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'cursects':
                //                        $System->system_driveTable->cur_sects->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'lbasects':
                //                        $System->system_driveTable->lba_sects->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'iordy':
                //                        $System->system_driveTable->io_rdy->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'tpio':
                //                        $System->system_driveTable->t_pio->setValue($keyValue[1]);
                //
                //                        break;
                //                    case 'tdma':
                //                        $System->system_driveTable->t_dma->setValue($keyValue[1]);
                //
                //                        break;
                //                    default:
                //                        $property = mb_strtolower(trim($keyValue[0]));
                //                        $System->system_driveTable->{$property}->setValue($keyValue[1]);
                //
                //                        break;
                //                }
            }
        }

        return self::SUCCESS;
    }
}
