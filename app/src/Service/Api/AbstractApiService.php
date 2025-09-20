<?php
declare(strict_types=1);

namespace App\Service\Api;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\ServiceUnavailableException;

/**
 * AbstractApiService Class
 *
 * This abstract class provides a foundation for API service classes,
 * handling common operations such as sending requests and error handling.
 */
abstract class AbstractApiService
{
    /**
     * @var \Cake\Http\Client The HTTP client used to send requests.
     */
    protected Client $client;

    /**
     * @var string The API key used for authentication.
     */
    protected string $apiKey;

    /**
     * @var string The base URL of the API.
     */
    protected string $apiUrl;

    /**
     * @var string The version of the API being used.
     */
    protected string $apiVersion;

    /**
     * AbstractApiService constructor.
     *
     * @param \Cake\Http\Client $client The HTTP client instance.
     * @param string $apiKey The API key for authentication.
     * @param string $apiUrl The base URL of the API.
     * @param string $apiVersion The version of the API.
     */
    public function __construct(Client $client, string $apiKey, string $apiUrl, string $apiVersion)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->apiVersion = $apiVersion;
    }

    /**
     * Sends a POST request to the API with the given payload.
     *
     * @param array $payload The data to be sent in the request body.
     * @return \Cake\Http\Client\Response The response from the API.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails.
     */
    public function sendRequest(array $payload, int $timeOut = 30): Response
    {
        $response = $this->client->post(
            $this->apiUrl,
            json_encode($payload),
            [
                'headers' => $this->getHeaders(),
                'timeout' => $timeOut,
            ],
        );

        if (!$response->isOk()) {
            $this->handleApiError($response);
        }

        return $response;
    }

    /**
     * Gets the headers for the API request.
     *
     * @return array An associative array of headers.
     */
    abstract protected function getHeaders(): array;

    /**
     * Handles API errors by logging the error and throwing an exception.
     *
     * @param \Cake\Http\Client\Response $response The response from the API.
     * @throws \Cake\Http\Exception\ServiceUnavailableException Always thrown to indicate the API request failed.
     */
    protected function handleApiError(Response $response): void
    {
        $errorBody = $response->getStringBody();
        $statusCode = $response->getStatusCode();

        throw new ServiceUnavailableException(sprintf(
            'API request error: Status Code %s Body: %s Reason: %s',
            $statusCode,
            $errorBody,
            $response->getReasonPhrase(),
        ));
    }

    /**
     * Parses the API response.
     *
     * @param \Cake\Http\Client\Response $response The response from the API.
     * @return array The parsed response data.
     */
    abstract protected function parseResponse(Response $response): array;
}
