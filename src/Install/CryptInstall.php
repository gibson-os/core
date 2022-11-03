<?php
declare(strict_types=1);

namespace GibsonOS\Core\Install;

use GibsonOS\Core\Dto\Install\Configuration;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class CryptInstall extends AbstractInstall implements PriorityInterface, SingleInstallInterface
{
    public function install(string $module): \Generator
    {
        try {
            $hashAlgorithm = $this->envService->getString('PASSWORD_HASH_ALGO');
        } catch (GetError) {
            yield $hashAlgorithmInput = $this->getEnvInput(
                'PASSWORD_HASH_ALGO',
                implode(PHP_EOL, hash_algos()) . PHP_EOL .
                    'Which hash algorithm should be used?'
            );
            $hashAlgorithm = $hashAlgorithmInput->getValue() ?? '';

            if (!in_array($hashAlgorithm, hash_algos())) {
                throw new InstallException(sprintf('Hash algorithm "%s" not allowed!', $hashAlgorithm));
            }
        }

        try {
            $hashSalt = $this->envService->getString('PASSWORD_HASH_SALT');
        } catch (GetError) {
            $hashSalt = '';
            $saltLength = mt_rand(100, 150);

            for ($i = 0; $i < $saltLength; ++$i) {
                $hashSalt .= chr(mt_rand(32, 255));
            }

            yield new Success('Hash salt generated!');
        }

        try {
            $cryptAlgorithm = $this->envService->getString('CRYPT_ALGO');
        } catch (GetError) {
            yield $cryptAlgorithmInput = $this->getEnvInput(
                'CRYPT_ALGO',
                implode(PHP_EOL, openssl_get_cipher_methods()) . PHP_EOL .
                'Which crypt algorithm should be used?'
            );
            $cryptAlgorithm = $cryptAlgorithmInput->getValue() ?? '';

            if (!in_array($cryptAlgorithm, openssl_get_cipher_methods())) {
                throw new InstallException(sprintf('Crypt algorithm "%s" not allowed!', $hashAlgorithm));
            }
        }

        try {
            $cryptSalt = $this->envService->getString('CRYPT_SALT');
        } catch (GetError) {
            $cryptSalt = '';
            $saltLength = mt_rand(100, 150);

            for ($i = 0; $i < $saltLength; ++$i) {
                $cryptSalt .= chr(mt_rand(32, 255));
            }

            yield new Success('Crypt salt generated!');
        }

        try {
            $cryptInitializationVector = $this->envService->getString('CRYPT_INITIALIZATION_VECTOR');
        } catch (GetError) {
            $cryptInitializationVector = '';

            for ($i = 0; $i < 16; ++$i) {
                $cryptInitializationVector .= chr(mt_rand(32, 255));
            }

            yield new Success('Crypt salt generated!');
        }

        yield (new Configuration('Crypt configuration generated!'))
            ->setValue('PASSWORD_HASH_ALGO', $hashAlgorithm)
            ->setValue('PASSWORD_HASH_SALT', $hashSalt)
            ->setValue('CRYPT_ALGO', $cryptAlgorithm)
            ->setValue('CRYPT_SALT', $cryptSalt)
            ->setValue('CRYPT_INITIALIZATION_VECTOR', $cryptInitializationVector)
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
