<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Model\SmartAttribute;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class SmartAttributeData extends AbstractInstall implements PriorityInterface
{
    public function install(string $module): Generator
    {
        $this
            ->setSmartAttribute(1, 'Read Error Rate', '(Vendor specific raw value.) Stores data related to the rate of hardware read errors that occurred when reading data from a disk surface. The raw value has different structure for different vendors and is often not meaningful as a decimal number.')
            ->setSmartAttribute(2, 'Throughput Performance', 'Overall (general) throughput performance of a hard disk drive. If the value of this attribute is decreasing there is a high probability that there is a problem with the disk.')
            ->setSmartAttribute(3, 'Spin-Up Time', 'Average time of spindle spin up (from zero RPM to fully operational [millisecs]).')
            ->setSmartAttribute(4, 'Start/Stop Count', 'A tally of spindle start/stop cycles. The spindle turns on, and hence the count is increased, both when the hard disk is turned on after having before been turned entirely off (disconnected from power source) and when the hard disk returns from having previously been put to sleep mode.[14]')
            ->setSmartAttribute(5, 'Reallocated Sectors Count', 'Count of reallocated sectors. When the hard drive finds a read/write/verification error, it marks that sector as "reallocated" and transfers data to a special reserved area (spare area). This process is also known as remapping, and reallocated sectors are called "remaps". The raw value normally represents a count of the bad sectors that have been found and remapped. Thus, the higher the attribute value, the more sectors the drive has had to reallocate. This allows a drive with bad sectors to continue operation; however, a drive which has had any reallocations at all is significantly more likely to fail in the near future.[2] While primarily used as a metric of the life expectancy of the drive, this number also affects performance. As the count of reallocated sectors increases, the read/write speed tends to become worse because thedrive head is forced to seek to the reserved area whenever a remap is accessed. A workaround which will preserve drive speed at the expense of capacity is to create adisk partition over the region which contains remaps and instruct the operating system to not use that partition.')
            ->setSmartAttribute(6, 'Read Channel Margin', 'Margin of a channel while reading data. The function of this attribute is not specified.')
            ->setSmartAttribute(7, 'Seek Error Rate', '(Vendor specific raw value.) Rate of seek errors of the magnetic heads. If there is a partial failure in the mechanical positioning system, then seek errors will arise. Such a failure may be due to numerous factors, such as damage to a servo, or thermal widening of the hard disk. The raw value has different structure for different vendors and is often not meaningful as a decimal number.')
            ->setSmartAttribute(8, 'Seek Time Performance', 'Average performance of seek operations of the magnetic heads. If this attribute is decreasing, it is a sign of problems in the mechanical subsystem.')
            ->setSmartAttribute(9, 'Power-On Hours (POH)', 'Count of hours in power-on state. The raw value of this attribute shows total count of hours (or minutes, or seconds, depending on manufacturer) in power-on state.[15]')
            ->setSmartAttribute(10, 'Spin Retry Count', 'Count of retry of spin start attempts. This attribute stores a total count of the spin start attempts to reach the fully operational speed (under the condition that the first attempt was unsuccessful). An increase of this attribute value is a sign of problems in the hard disk mechanical subsystem.')
            ->setSmartAttribute(11, 'Recalibration Retries orCalibrat', 'This attribute indicates the count that recalibration was requested (under the condition that the first attempt was unsuccessful). An increase of this attribute value is a sign of problems in the hard disk mechanical subsystem.')
            ->setSmartAttribute(12, 'Power Cycle Count', 'This attribute indicates the count of full hard disk power on/off cycles.')
            ->setSmartAttribute(13, 'Soft Read Error Rate', 'Uncorrected read errors reported to the operating system.')
            ->setSmartAttribute(180, 'Unused Reserved Block Count Tota', '"Pre-Fail" Attribute used at least in HP devices.')
            ->setSmartAttribute(183, 'SATA Downshift Error Count', 'Western Digital and Samsung attribute.')
            ->setSmartAttribute(184, 'End-to-End error / IOEDC', 'This attribute is a part of Hewlett-Packard\'s SMART IV technology, as well as part of other vendors\' IO Error Detection and Correction schemas, and it contains a count of parity errors which occur in the data path to the media via the drive\'s cache RAM.[16]')
            ->setSmartAttribute(185, 'Head Stability', 'Western Digital attribute.')
            ->setSmartAttribute(186, 'Induced Op-Vibration Detection', 'Western Digital attribute.')
            ->setSmartAttribute(187, 'Reported Uncorrectable Errors', 'The count of errors that could not be recovered using hardware ECC (see attribute 195).')
            ->setSmartAttribute(188, 'Command Timeout', 'The count of aborted operations due to HDD timeout. Normally this attribute value should be equal to zero and if the value is far above zero, then most likely there will be some serious problems with power supply or an oxidized data cable.[17]')
            ->setSmartAttribute(189, 'High Fly Writes', 'HDD producers implement a Fly Height Monitor that attempts to provide additional protections for write operations by detecting when a recording head is flying outside its normal operating range. If an unsafe fly height condition is encountered, the write process is stopped, and the information is rewritten or reallocated to a safe region of the hard drive. This attribute indicates the count of these errors detected over the lifetime of the drive. This feature is implemented in most modern Seagate drives[1] and some of Western Digital’s drives, beginning with the WD Enterprise WDE18300 and WDE9180 Ultra2 SCSI hard drives, and will be included on all future WD Enterprise products.[18]')
            ->setSmartAttribute(190, 'Airflow Temperature (WDC) resp.A', 'Airflow temperature on Western Digital HDs (Same as temp. [C2 ) but current value is 50 less for some models. Marked as obsolete.)')
            ->setSmartAttribute(191, 'G-sense Error Rate', 'The count of errors resulting from externally-induced shock & vibration.')
            ->setSmartAttribute(192, 'Power-off Retract Countor Emerge', 'Count of times the heads are loaded off the media. Heads can be unloaded without actually powering off.[citation needed]')
            ->setSmartAttribute(193, 'Load Cycle Count orLoad/Unload C', 'Count of load/unload cycles into head landing zone position.[19] The typical lifetime rating for laptop (2.5-in) hard drives is 300,000 to 600,000 load cycles.[20] Some laptop drives are programmed to unload the heads whenever there has not been any activity for about five seconds.[21] Many Linux installations write to the file system a few times a minute in the background.[22] As a result, there may be 100 or more load cycles per hour, and the load cycle rating may be exceeded in less than a year.[23]')
            ->setSmartAttribute(194, 'Temperatureresp.Temperature Cels', 'Current internal temperature.')
            ->setSmartAttribute(195, 'Hardware ECC Recovered', '(Vendor specific raw value.) The raw value has different structure for different vendors and is often not meaningful as a decimal number.')
            ->setSmartAttribute(196, 'Reallocation Event Count', 'Count of remap operations. The raw value of this attribute shows the total count of attempts to transfer data from reallocated sectors to a spare area. Both successful & unsuccessful attempts are counted.[24]')
            ->setSmartAttribute(197, 'Current Pending Sector Count', 'Count of "unstable" sectors (waiting to be remapped, because of read errors). If an unstable sector is subsequently read successfully, this value is decreased and the sector is not remapped. Read errors on a sector will not remap the sector (since it might be readable later); instead, the drive firmware remembers that the sector needs to be remapped, and remaps it the next time it\'s written.[25]')
            ->setSmartAttribute(198, 'Uncorrectable Sector Countor Off', 'The total count of uncorrectable errors when reading/writing a sector. A rise in the value of this attribute indicates defects of the disk surface and/or problems in the mechanical subsystem.')
            ->setSmartAttribute(199, 'UltraDMA CRC Error Count', 'The count of errors in data transfer via the interface cable as determined by ICRC (Interface Cyclic Redundancy Check).')
            ->setSmartAttribute(200, 'Multi-Zone Error Rate [26]', 'The count of errors found when writing a sector. The higher the value, the worse the disk\'s mechanical condition is.')
            ->setSmartAttribute(201, 'Soft Read Error Rate orTA Count', 'Count of off-track errors.')
            ->setSmartAttribute(202, 'Data Address Mark errors or TA C', 'Count of Data Address Mark errors (or vendor-specific).[citation needed]')
            ->setSmartAttribute(203, 'Run Out Cancel', 'Count of ECC errors')
            ->setSmartAttribute(204, 'Soft ECC Correction', 'Count of errors corrected by software ECC[citation needed]')
            ->setSmartAttribute(205, 'Thermal Asperity Rate (TAR)', 'Count of errors due to high temperature.[17]')
            ->setSmartAttribute(206, 'Flying Height', 'Height of heads above the disk surface. A flying height that\'s too low increases the chances of a head crash while a flying height that\'s too high increases the chances of a read/write error.[citation needed]')
            ->setSmartAttribute(207, 'Spin High Current', 'Amount of surge current used to spin up the drive.[17]')
            ->setSmartAttribute(208, 'Spin Buzz', 'Count of buzz routines needed to spin up the drive due to insufficient power.[17]')
            ->setSmartAttribute(209, 'Offline Seek Performance', 'Drive’s seek performance during its internal tests.[17]')
            ->setSmartAttribute(210, 'Vibration During Write', '(found in a Maxtor 6B200M0 200GB and Maxtor 2R015H1 15GB disks)')
            ->setSmartAttribute(211, 'Vibration During Write', 'Vibration During Write[citation needed]')
            ->setSmartAttribute(212, 'Shock During Write', 'Shock During Write[citation needed]')
            ->setSmartAttribute(220, 'Disk Shift', 'Distance the disk has shifted relative to the spindle (usually due to shock or temperature). Unit of measure is unknown.')
            ->setSmartAttribute(221, 'G-Sense Error Rate', 'The count of errors resulting from externally-induced shock & vibration.')
            ->setSmartAttribute(222, 'Loaded Hours', 'Time spent operating under data load (movement of magnetic head armature)[citation needed]')
            ->setSmartAttribute(223, 'Load/Unload Retry Count', 'Count of times head changes position.[citation needed]')
            ->setSmartAttribute(224, 'Load Friction', 'Resistance caused by friction in mechanical parts while operating.[citation needed]')
            ->setSmartAttribute(225, 'Load/Unload Cycle Count', 'Total count of load cycles[citation needed]')
            ->setSmartAttribute(226, 'Load \'In\'-time', 'Total time of loading on the magnetic heads actuator (time not spent in parking area).[citation needed]')
            ->setSmartAttribute(227, 'Torque Amplification Count', 'Count of attempts to compensate for platter speed variations[citation needed]')
            ->setSmartAttribute(228, 'Power-Off Retract Cycle', 'The count of times the magnetic armature was retracted automatically as a result of cutting power.[citation needed]')
            ->setSmartAttribute(230, 'GMR Head Amplitude', 'Amplitude of "thrashing" (distance of repetitive forward/reverse head motion)[citation needed]')
            ->setSmartAttribute(231, 'Temperature', 'Drive Temperature')
            ->setSmartAttribute(232, 'Endurance Remaining', 'Number of physical erase cycles completed on the drive as a percentage of the maximum physical erase cycles the drive is designed to endure')
            ->setSmartAttribute(233, 'Power-On Hours', 'Number of hours elapsed in the power-on state.')
            ->setSmartAttribute(240, 'Head Flying Hours', 'Time while head is positioning[citation needed]')
            ->setSmartAttribute(241, 'Total LBAs Written', 'Total count of LBAs written')
            ->setSmartAttribute(242, 'Total LBAs Read', 'Total count of LBAs read. Some S.M.A.R.T. utilities will report a negative number for the raw value since in reality it has 48 bits rather than 32.')
            ->setSmartAttribute(250, 'Read Error Retry Rate', 'Count of errors while reading from a disk')
            ->setSmartAttribute(254, 'Free Fall Protection', 'Count of "Free Fall Events" detected [29]')
        ;

        yield new Success('Smart Attributes installed!');
    }

    private function setSmartAttribute(int $id, string $short, string $description): SmartAttributeData
    {
        $this->logger->info(sprintf('Add smart attribute #%d "%s"', $id, $short));
        (new SmartAttribute())
            ->setId($id)
            ->setShort($short)
            ->setDescription($description)
            ->save()
        ;

        return $this;
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
