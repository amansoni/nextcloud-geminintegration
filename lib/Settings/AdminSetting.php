<?php
declare(strict_types=1);

namespace OCA\GeminiIntegration\Settings;

use OCP\Settings\ISettings;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IL10N;
use OCP\Template; // Use Template directly if needed, but ISettings provides structure

/**
 * Configuration (Storing the API Key Securely)
 */
class AdminSettings implements ISettings {

    private IConfig $config;
    private IURLGenerator $urlGenerator;
    private IL10N $l10n;
    private string $appName;

    public function __construct(IConfig $config, IURLGenerator $urlGenerator, IL10N $l10n, string $appName) {
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->l10n = $l10n;
        $this->appName = $appName;
    }

    /**
     * @return TemplateResponse
     */
     public function getForm(): Template {
         $apiKey = $this->config->getAppValue($this->appName, 'gemini_api_key', '');
         // You might want other settings like default model, project ID etc.

        $parameters = [
            'gemini_api_key' => $apiKey,
             // 'gemini_project_id' => $this->config->getAppValue($this->appName, 'gemini_project_id', ''),
        ];

        return new Template($this->appName, 'admin', 'blank', $parameters); // 'blank' layout
     }

    public function getSectionID(): string {
        // Must match a section ID defined in settings/admin.php or a default one
        return 'additional'; // Or 'connected-accounts', 'workflow', etc.
    }

    /**
     * Display priority order. Lower means higher priority.
     */
    public function getPriority(): int {
        return 50; // Adjust as needed
    }
}