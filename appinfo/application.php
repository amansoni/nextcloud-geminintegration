<?php
declare(strict_types=1);

namespace OCA\GeminiIntegration\AppInfo;

use OCA\GeminiIntegration\Controller\GeminiController;
use OCA\GeminiIntegration\Service\GeminiService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {

    public const APP_ID = 'geminintegration';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    // IBootstrap interface methods
    public function register(IRegistrationContext $context): void {
        // Register Controllers
        $context->registerController(GeminiController::class, function ($server) {
            return new GeminiController(
                $server->get(self::APP_ID), // App name
                $server->getRequest(),      // Request object
                $server->get(GeminiService::class), // Inject our Gemini Service
                $server->get(\OCP\ILogger::class), // Inject Logger
                $server->query(\OCP\Files\IRootFolder::class), // Inject Root Folder for file access
                $server->get(\OCP\IUserSession::class) // Inject User Session
            );
        });

        // Register Services
        $context->registerService(GeminiService::class, function ($server) {
            return new GeminiService(
                $server->query(\OCP\Http\Client\IClientService::class), // HTTP Client Service
                $server->query(\OCP\IConfig::class), // Config to get API Key
                $server->get(\OCP\ILogger::class),  // Logger
                self::APP_ID // Pass App ID for config retrieval
            );
        });

        // Register File Action (using JS)
         $context->registerScript('gemini-file-action'); // We'll create this JS file later

        // Register Navigation entry if needed (not for this simple example)
        // Register Background Jobs if needed
    }

    public function boot(IBootContext $context): void {
        // Stuff to do on app boot, like registering event listeners if needed
    }
}