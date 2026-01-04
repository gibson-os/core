<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Exception\CryptException;
use GibsonOS\Core\Exception\FileNotFound;

class CryptService
{
    public function __construct(
        #[GetEnv('CRYPT_INITIALIZATION_VECTOR')]
        private readonly string $cryptInitializationVector,
        #[GetEnv('CRYPT_ALGO')]
        private readonly string $cryptAlgo,
        #[GetEnv('CRYPT_SALT')]
        private readonly string $cryptSalt,
    ) {
    }

    public function encrypt(string $data): string
    {
        $initializationVector = substr(
            $this->cryptInitializationVector,
            0,
            (int) openssl_cipher_iv_length($this->cryptAlgo),
        );

        $encrypted = openssl_encrypt($data, $this->cryptAlgo, $this->cryptSalt, 0, $initializationVector);

        if ($encrypted === false) {
            throw new CryptException('Encryption failed');
        }

        return $encrypted;
    }

    /**
     * @throws FileNotFound
     */
    public function encryptFile(string $inputPath, string $outputPath): void
    {
        $inputFile = fopen($inputPath, 'rb');

        if (!is_resource($inputFile)) {
            throw new FileNotFound();
        }

        $outputFile = fopen($outputPath, 'wb');

        if (!is_resource($outputFile)) {
            throw new FileNotFound();
        }

        $options = [
            'iv' => $this->cryptInitializationVector,
            'key' => $this->cryptSalt,
            'mode' => 'cbc',
        ];
        stream_filter_append($outputFile, 'mcrypt.rijndael-256', STREAM_FILTER_WRITE, $options);

        while (!feof($inputFile)) {
            fwrite($outputFile, (string) fread($inputFile, 8192));
        }

        fclose($inputFile);
        fclose($outputFile);
    }

    public function decrypt(string $data): string
    {
        $initializationVector = substr(
            $this->cryptInitializationVector,
            0,
            (int) openssl_cipher_iv_length($this->cryptAlgo),
        );

        $decrypted = openssl_decrypt($data, $this->cryptAlgo, $this->cryptSalt, 0, $initializationVector);

        if ($decrypted === false) {
            throw new CryptException('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * @throws FileNotFound
     */
    public function decryptFile(string $inputPath, string $outputPath): void
    {
        $inputFile = fopen($inputPath, 'rb');

        if (!is_resource($inputFile)) {
            throw new FileNotFound();
        }

        $outputFile = fopen($outputPath, 'wb');

        if (!is_resource($outputFile)) {
            throw new FileNotFound();
        }

        $options = [
            'iv' => $this->cryptInitializationVector,
            'key' => $this->cryptSalt,
            'mode' => 'cbc',
        ];
        stream_filter_append($inputFile, 'mdecrypt.rijndael-256', STREAM_FILTER_READ, $options);

        while (!feof($inputFile)) {
            fwrite($outputFile, (string) fread($inputFile, 8192));
        }

        fclose($inputFile);
        fclose($outputFile);
    }
}
