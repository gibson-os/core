<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Dto\Web\Body;
use GibsonOS\Core\Dto\Web\Request;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Utility\JsonUtility;
use Google\Auth\CredentialsLoader;
use JsonException;
use Psr\Log\LoggerInterface;

class FcmService
{
    private const URL = 'https://fcm.googleapis.com/v1/projects/';

    private string $url;

    public function __construct(
        #[GetSetting('fcmKey', 'core')] private Setting $key,
        #[GetSetting('fcmProjectId', 'core')] private Setting $projectId,
        private WebService $webService,
        private LoggerInterface $logger
    ) {
        $this->url = self::URL . $this->projectId->getValue() . '/';
    }

    /**
     * @throws WebException
     * @throws JsonException
     */
    public function pushMessage(Message $message): FcmService
    {
        $credentials = CredentialsLoader::makeCredentials(
            [$this->url . 'messages:send'],
            ['key' => $this->key->getValue()]
        );

        $content = JsonUtility::encode($message);
        $request = (new Request($this->url . 'messages:send'))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $credentials->fetchAuthToken()['access_token'],
            ])
            ->setBody((new Body())->setContent($content, mb_strlen($content)))
        ;

        $response = $this->webService->post($request);

        $this->logger->debug(sprintf('FCM push response: %s', $response->getBody()->getContent()));

        return $this;
    }
}
