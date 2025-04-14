<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */ // Contains variables passed from AdminSettings::getForm

script('geminintegration', 'admin-settings'); // Optional: if you need JS for settings page
style('geminintegration', 'admin-settings');   // Optional: if you need CSS

?>
<div id="geminiIntegrationSettings" class="section">
    <h2><?php p($l->t('Gemini Integration Settings')); ?></h2>

    <p class="settings-hint"><?php p($l->t('Configure connection to the Google Gemini API. You need an API key from Google Cloud Console.')); ?></p>

    <label for="geminiApiKey"><?php p($l->t('Gemini API Key')); ?></label>
    <input type="password" id="geminiApiKey" name="gemini_api_key"
           value="<?php p($_['gemini_api_key']); ?>"
           placeholder="<?php p($l->t('Enter your Gemini API Key')); ?>" />
    <button id="saveGeminiSettings" type="button"><?php p($l->t('Save Settings')); ?></button>
    <span id="geminiSettingsMsg" class="settings-message"></span>

    <!-- Add fields for Project ID, Model Selection etc. if needed -->

</div>