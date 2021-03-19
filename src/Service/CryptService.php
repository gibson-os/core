<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Exception\FileNotFound;

class CryptService
{
    private string $cryptInitializationVector;

    private string $cryptAlgo;

    private string $cryptSalt;

    public function __construct(EnvService $envService)
    {
        $this->cryptInitializationVector = $envService->getString('CRYPT_INITIALIZATION_VECTOR');
        $this->cryptAlgo = $envService->getString('CRYPT_ALGO');
        $this->cryptSalt = $envService->getString('CRYPT_SALT');
    }

    public function encrypt(string $data): string
    {
        $initializationVector = substr(
            $this->cryptInitializationVector,
            0,
            (int) openssl_cipher_iv_length($this->cryptAlgo)
        );

        return openssl_encrypt($data, $this->cryptAlgo, $this->cryptSalt, 0, $initializationVector);
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

    public function decrypt($data): string
    {
        $initializationVector = substr(
            $this->cryptInitializationVector,
            0,
            (int) openssl_cipher_iv_length($this->cryptAlgo)
        );

        return openssl_decrypt($data, $this->cryptAlgo, $this->cryptSalt, 1, $initializationVector);
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
