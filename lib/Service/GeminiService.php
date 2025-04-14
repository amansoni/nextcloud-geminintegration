<?php
declare(strict_types=1);

namespace OCA\GeminiIntegration\Service;

use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\ILogger;

/***
 * This service handles communication with the Gemini API.
 */
class GeminiService {

    private IClient $client;
    private IConfig $config;
    private ILogger $logger;
    private string $apiKey;
    private string $appId;

    public function __construct(IClientService $clientService, IConfig $config, ILogger $logger, string $appId) {
        $this->client = $clientService->newClient();
        $this->config = $config;
        $this->logger = $logger;
        $this->appId = $appId;
        // Retrieve API key securely stored in Nextcloud's config
        $this->apiKey = $this->config->getAppValue($this->appId, 'gemini_api_key', '');
    }

    /**
     * Sends text to Gemini API for summarization.
     *
     * @param string $textToSummarize
     * @return string|null The summary text or null on error
     */
    public function summarizeText(string $textToSummarize): ?string {
        if (empty($this->apiKey)) {
            $this->logger->error('Gemini API Key is not configured.', ['app' => $this->appId]);
            return null;
        }

        // Use v1beta for now, check Google Docs for latest stable version endpoint
        // Note: Project ID might be needed in the URL for some Vertex AI endpoints,
        // but for the generative language API key auth, it's often not. Double check Google's docs.
        $apiUrl = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=%s',
            $this->apiKey
        );

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        // TODO: Add a better prompt
                        ['text' => "Please summarize the following text:\n\n" . $textToSummarize]
                    ]
                ]
            ],
             // Add generationConfig if needed (temperature, max output tokens etc)
             // 'generationConfig' => [
             //    'temperature' => 0.7,
             //    'maxOutputTokens' => 256,
             // ]
        ];

        try {
            $options = [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
                // Add timeout
                'timeout' => 60, // seconds
            ];

            $response = $this->client->post($apiUrl, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            if ($statusCode >= 200 && $statusCode < 300) {
                $responseData = json_decode($body, true);

                // Extract the text from the response - structure depends on Gemini API version
                // Check the API response structure carefully!
                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($responseData['candidates'][0]['content']['parts'][0]['text']);
                } else {
                     $this->logger->error('Unexpected Gemini API response structure: ' . $body, ['app' => $this->appId]);
                     // Log the full response if needed for debugging
                     // $this->logger->debug('Full Gemini Response: ' . $body, ['app' => $this->appId]);
                    return null;
                }
            } else {
                $this->logger->error(
                    "Gemini API request failed with status code $statusCode: $body",
                    ['app' => $this->appId]
                );
                return null;
            }

        } catch (\Exception $e) {
            $this->logger->logException($e, ['message' => 'Error calling Gemini API', 'app' => $this->appId]);
            return null;
        }
    }
}