// Wait for the Files app to be ready (or document ready)
OC.Plugins.register('OCA.Files.FileList', {
    attach: function (fileList) {
        // Add a new action for text files
        fileList.fileActions.registerAction({
            name: 'GeminiSummarize',
            displayName: t('geminintegration', 'Summarize with Gemini'), // Use translation function
            mime: 'text/plain', // Show only for text files
            permissions: OC.PERMISSION_READ,
            iconClass: 'icon-commenting', // Choose an appropriate icon
            actionHandler: function (filename, context) {
                const fileId = context.$file.data('id');
                const fileListItem = context.$file; // The jQuery element for the file row

                // Show some loading indicator
                fileListItem.addClass('loading'); // You might need specific CSS for this
                OC.Notification.show(t('geminintegration', 'Requesting summary from Gemini...'), { type: 'info', timeout: 3 });


                // Generate the URL to our controller endpoint
                const url = OC.generateUrl(`/apps/geminintegration/files/${fileId}/summarize`);

                // Make AJAX request to the backend controller
                $.ajax({
                    url: url,
                    type: 'GET', // Or POST if you prefer
                    // Add CSRF token if using POST and not disabling CSRF check
                    // headers: {
                    //    'requesttoken': OC.requestToken
                    // },
                    success: function (response) {
                        fileListItem.removeClass('loading');
                        if (response.summary) {
                            // Display the summary - using a modal is often best
                            OC.dialogs.info(
                                response.summary, // Content
                                t('geminintegration', 'Gemini Summary for {filename}', {filename: filename}), // Title
                                null, // Callback (optional)
                                true // Modal? yes
                            );
                        } else if (response.message) {
                            OC.Notification.show(response.message, { type: 'info' });
                        }
                         else {
                            OC.Notification.show(response.error || t('geminintegration', 'Failed to get summary.'), { type: 'error' });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        fileListItem.removeClass('loading');
                        console.error('Gemini Summarize Error:', textStatus, errorThrown, jqXHR.responseJSON);
                         let errorMsg = t('geminintegration', 'Error requesting summary.');
                         if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                            errorMsg = jqXHR.responseJSON.error;
                         }
                        OC.Notification.show(errorMsg, { type: 'error' });
                    }
                });
            }
        });
    }
});

// Add translations (optional but good practice)
// Create l10n/en.json (and other languages)
// {
//     "Summarize with Gemini": "Summarize with Gemini",
//     "Requesting summary from Gemini...": "Requesting summary from Gemini...",
//     "Gemini Summary for {filename}": "Gemini Summary for {filename}",
//     "Failed to get summary.": "Failed to get summary.",
//     "Error requesting summary.": "Error requesting summary.",
//     "Only plain text files can be summarized": "Only plain text files can be summarized",
//     "File is empty, nothing to summarize.": "File is empty, nothing to summarize.",
//     "File not found": "File not found",
//     "An unexpected error occurred": "An unexpected error occurred",
//     "Gemini API Key is not configured.": "Gemini API Key is not configured."
// }
// You'd need to run `occ l10n:generate geminintegration en` etc.