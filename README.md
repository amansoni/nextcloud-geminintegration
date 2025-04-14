# nextcloud-geminintegration
Custom Nextcloud app that integrates with the Google Gemini API. Adds a file action to .txt files. When clicked, it sends the file content to the Gemini API for summarization and displays the summary.

## Prerequisites & Setup
### Google Cloud Project & Gemini API Key:

* Go to the [Google Cloud Console](https://console.cloud.google.com/)
* Create a new project or select an existing one.
* Enable the Vertex AI API.
    * Create an API Key under "APIs & Services" > "Credentials". Restrict this key (e.g., by IP address if possible, though tricky for a self-hosted Nextcloud, or ideally use service accounts for server-to-server, but API keys are simpler for this example). Keep this key secure!
    Note your Google Cloud Project ID.
    Familiarize yourself with the Gemini API documentation and the REST API reference (specifically the generateContent method). You'll likely use the gemini-pro model for text tasks.

### Nextcloud Development Environment:
* A running Nextcloud instance (ideally a dedicated development instance).
* Access to the server's command line.
* PHP (matching your Nextcloud version), Composer.
* Enable Nextcloud's developer mode (occ config:app:set core debug --value=true).
* Basic understanding of Nextcloud App Development.

* Enable the app
```
php /path/to/your/nextcloud/occ app:enable geminintegration
```
* Install Guzzle (HTTP Client): Nextcloud usually bundles Guzzle, but managing dependencies via Composer is cleaner if you add more. For now, we'll use the built-in client service.

```
# In the geminintegration directory
composer require guzzlehttp/guzzle
php /path/to/nextcloud/occ maintenance:mode --on
php /path/to/nextcloud/occ app:update --all # Or php occ app:disable geminintegration && php occ app:enable geminintegration
php /path/to/nextcloud/occ maintenance:mode --off
```

### Testing and Debugging

* Debugging: Use occ log:watch or check nextcloud.log. Add $this->logger->debug(...) calls in PHP code. Use browser developer tools (Network tab, Console).
* Error Handling: Improve error handling in GeminiService and GeminiController to provide more informative messages to the user and logs. Handle API rate limits, invalid responses, network issues.

### Security and Further Development

* NEVER commit your API key to Git.
* Use Nextcloud's secure configuration storage.
* Sanitize all input.
* Validate file access permissions rigorously.
* Consider CSRF protection for all state-changing requests.
* User Experience: Use modals for results, provide loading indicators, clear notifications.
* Translations: Implement proper localization using .po/.json files.
* Packaging: Package app for distribution (occ app:pack geminintegration).
