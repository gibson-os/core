<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Exception\FcmException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Utility\JsonUtility;
use Google\Auth\CredentialsLoader;
use Psr\Log\LoggerInterface;

class FcmService
{
    private const URL = 'https://fcm.googleapis.com/v1/projects/';

    private string $url;

    public function __construct(
        #[GetEnv('FCM_PROJECT_ID')] private string $projectId,
        #[GetEnv('GOOGLE_APPLICATION_CREDENTIALS')] private string $googleCredentialFile,
        private WebService $webService,
        private LoggerInterface $logger
    ) {
        $this->url = self::URL . $this->projectId . '/';
    }

    /**
     * @throws WebException
     * @throws FcmException
     * @throws \JsonException
     */
    public function pushMessage(Message $message): FcmService
    {
        $credentials = CredentialsLoader::makeCredentials(
            ['https://www.googleapis.com/auth/cloud-platform'],
            JsonUtility::decode(file_get_contents($this->googleCredentialFile))
        );
        $authToken = $credentials->fetchAuthToken();

        if (!isset($authToken['access_token'])) {
            throw new FcmException('Access token not in googles oauth response!');
        }

        $content = JsonUtility::encode(['message' => $message]);
        $request = (new Request($this->url . 'messages:send'))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $authToken['access_token'],
            ])
            ->setBody((new Body())->setContent($content, mb_strlen($content)))
        ;

        $response = $this->webService->post($request);
        $body = $response->getBody()->getContent();
        $this->logger->debug(sprintf('FCM push response: %s', $body));
        $body = JsonUtility::decode($body);

        if (isset($body['error'])) {
            throw new FcmException($body['error']['message'], $body['error']['code']);
        }

        return $this;
    }
}
