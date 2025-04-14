$(document).ready(function () {
    $('#saveGeminiSettings').on('click', function () {
        const apiKey = $('#geminiApiKey').val().trim();
        const msgSpan = $('#geminiSettingsMsg');

        msgSpan.text(t('geminintegration', 'Saving...')).removeClass('success error').addClass('warning');

        // Endpoint for saving settings (Nextcloud provides this implicitly)
        // We just need to post to the app's settings path
         // Create a dedicated controller endpoint for better control if needed
         // For simplicity, let's use the core settings save mechanism (less ideal for validation)

         // Better: Create a controller endpoint for saving
        const url = OC.generateUrl('/apps/geminintegration/settings/admin'); // Need to define this route

         $.ajax({
            method: 'PUT', // Or POST
            url: url,
            data: {
                gemini_api_key: apiKey
                // Add other settings here
            },
            success: function (response) {
                 if(response && response.saved) {
                    msgSpan.text(t('geminintegration', 'Settings saved successfully.')).removeClass('warning error').addClass('success');
                    // Optionally clear password field after save?
                    // $('#geminiApiKey').val(''); // Consider security implications
                 } else {
                    msgSpan.text(response.error || t('geminintegration', 'Failed to save settings.')).removeClass('warning success').addClass('error');
                 }
            },
            error: function (jqXHR) {
                console.error("Error saving Gemini settings:", jqXHR);
                msgSpan.text(t('geminintegration', 'Error saving settings: {error}', {error: jqXHR.responseJSON?.error || jqXHR.statusText }))
                       .removeClass('warning success').addClass('error');
            },
             complete: function() {
                 // Optionally re-enable button etc.
             }
        });

        // You'll need to:
        // 1. Create a route for PUT /apps/geminintegration/settings/admin in application.php
        // 2. Create a controller method (e.g., AdminSettingsController::saveSettings)
        // 3. Inject IConfig and save the value: $this->config->setAppValue($this->appName, 'gemini_api_key', $apiKey);
        // 4. Return a JSONResponse indicating success/failure.
    });
});