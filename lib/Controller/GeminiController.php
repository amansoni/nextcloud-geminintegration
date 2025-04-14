<?php
declare(strict_types=1);

namespace OCA\GeminiIntegration\Controller;

use OCA\GeminiIntegration\Service\GeminiService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse; // Use JSONResponse for API-like responses
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\ILogger;

/**
 * This handles requests from the frontend.
 */
class GeminiController extends Controller {

    private GeminiService $service;
    private ILogger $logger;
    private IRootFolder $rootFolder;
    private ?string $userId;

    public function __construct(
        string $appName,
        IRequest $request,
        GeminiService $service,
        ILogger $logger,
        IRootFolder $rootFolder,
        IUserSession $userSession
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->logger = $logger;
        $this->rootFolder = $rootFolder;
        $user = $userSession->getUser();
        $this->userId = $user ? $user->getUID() : null;
    }

    /**
     * @NoAdminRequired // Allow regular users
     * @NoCSRFRequired // Actions via JS often handle CSRF differently, but consider adding protection if needed
     * @PublicPage // If you need it accessible without login (unlikely here)
     *
     * @param int $fileId The ID of the file to summarize
     * @return JSONResponse
     */
    #[PublicPage]
    public function summarizeFile(int $fileId): JSONResponse {
         if ($this->userId === null) {
            return new JSONResponse(['error' => 'User not logged in'], 401);
        }

        try {
            $userFolder = $this->rootFolder->getUserFolder($this->userId);
            $nodes = $userFolder->getById($fileId); // Returns array

             if (empty($nodes) || !($nodes[0] instanceof File)) {
                return new JSONResponse(['error' => 'File not found or not accessible'], 404);
            }
            $file = $nodes[0];

            // Optional: Add checks for file type, size limits, etc.
            if ($file->getMimetype() !== 'text/plain') {
                 return new JSONResponse(['error' => 'Only plain text files can be summarized'], 400);
            }

            $content = $file->getContent();

            if (empty($content)) {
                 return new JSONResponse(['message' => 'File is empty, nothing to summarize.'], 200); // Or 400?
            }

            // Add size limits if needed before sending to API
            // if (strlen($content) > 50000) { // Example limit
            //     return new JSONResponse(['error' => 'File is too large to summarize'], 413);
            // }


            $summary = $this->service->summarizeText($content);

            if ($summary !== null) {
                return new JSONResponse(['summary' => $summary]);
            } else {
                // Error logged in GeminiService
                return new JSONResponse(['error' => 'Failed to generate summary'], 500);
            }

        } catch (NotFoundException $e) {
             $this->logger->warning("File not found for summarization: ID $fileId", ['app' => $this->appName, 'exception' => $e]);
            return new JSONResponse(['error' => 'File not found'], 404);
        } catch (\Exception $e) {
            $this->logger->logException($e, ['message' => "Error summarizing file ID $fileId", 'app' => $this->appName]);
            return new JSONResponse(['error' => 'An unexpected error occurred'], 500);
        }
    }
}