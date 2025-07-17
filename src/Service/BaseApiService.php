<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Service;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Kernel;
use TigerMedia\Base\HttpClient\TigerClient;
use TigerMedia\TigerConnect\Core\Content\Logger\LoggerDefinition;

abstract class BaseApiService
{
    private string $orderNumber;
    private string $salesChannelId;

    public function __construct(
        private readonly TigerClient $client,
        public readonly LoggerInterface $logger
    )
    {
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * @param string $endpoint
     * @param mixed[] $query
     * @return mixed
     */
    public function get(
        string $endpoint,
        array $query = []
    ): mixed
    {
        try {
            $response = $this->client->get($endpoint, ['query' => $query]);
            $this->insertLog($this->jsonify($endpoint, $query, $response), LoggerDefinition::LOG_INFO);
        } catch (GuzzleException $exception) {
            $this->logger->critical("Failed to get response from [GET] [$endpoint]", ['exception' => $exception]);
            $this->insertLog($exception->getMessage(), LoggerDefinition::LOG_CRITICAL);
            return null;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $endpoint
     * @param mixed[] $body
     * @return mixed[]|null
     */
    public function post(
        string $endpoint,
        array $body = [],
    ): mixed
    {
        try {
            $response = $this->client->post($endpoint, ['json' => $body]);
            $this->insertLog($this->jsonify($endpoint, $body, $response), LoggerDefinition::LOG_INFO);
        } catch (GuzzleException $exception) {
            $this->logger->critical("Failed to get response from [POST] [$endpoint]", ['exception' => $exception]);
            $this->insertLog($exception->getMessage(), LoggerDefinition::LOG_CRITICAL);
            return null;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $endpoint
     * @param mixed[] $body
     * @return mixed[]|null
     */
    public function patch(
        string $endpoint,
        array $body = [],
    ): mixed
    {
        try {
            $response = $this->client->patch($endpoint, ['json' => $body]);
            $this->insertLog($this->jsonify($endpoint, $body, $response), LoggerDefinition::LOG_INFO);
        } catch (GuzzleException $exception) {
            $this->logger->critical("Failed to get response from [PATCH] [$endpoint]", ['exception' => $exception]);
            $this->insertLog($exception->getMessage(), LoggerDefinition::LOG_CRITICAL);
            return null;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * @param string $endpoint
     * @param mixed[] $body
     * @return void
     */
    public function delete(
        string $endpoint,
        array $body = []
    ): void
    {
        try {
            $response = $this->client->delete($endpoint, ['query' => $body]);
            $this->insertLog($this->jsonify($endpoint, $body, $response), LoggerDefinition::LOG_INFO);
        } catch (GuzzleException $exception) {
            $this->logger->critical("Failed removing the order.", ['exception' => $exception]);
            $this->insertLog($exception->getMessage(), LoggerDefinition::LOG_CRITICAL);
        }
    }

    /**
     * @param string $endpoint
     * @param mixed[] $query
     * @param ResponseInterface $response
     * @return string
     */
    private function jsonify(
        string $endpoint,
        array $query,
        ResponseInterface $response,
    ): string
    {
        $jsonify = json_encode([
            'endpoint' => $endpoint,
            'query'    => $query,
            'response' => $response->getBody()->getContents()
        ]);
        $response->getBody()->rewind();
        return $jsonify;
    }

    private function insertLog(
        string $message,
        string $level
    ): void
    {
        $connection = Kernel::getConnection();
        $query = "INSERT INTO `tiger_connect_logger` (`id`, `message`, `level`, `order_number`, `created_at`) VALUES (UNHEX(:id), :message, :level, :orderNumber, :timestamp)";
        try {
            $connection->executeStatement($query, [
                'id'          => Uuid::randomHex(),
                'message'     => $message,
                'level'       => $level,
                'orderNumber' => $this->getOrderNumber(),
                'timestamp'   => (new DateTimeImmutable())->setTimezone(new DateTimeZone('Europe/Copenhagen'))->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]);
        } catch (\Exception $exception) {
            $this->logger->critical('Error on inserting logs.', ['exception' => $exception]);
        }
    }
}