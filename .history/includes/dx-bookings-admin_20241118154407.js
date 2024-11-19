jQuery(document).ready(function($) {
    $('.upload-document-button').click(function(e) {
        e.preventDefault();

        var buttonId = $(this).attr('id'); 
        var appointmentId = buttonId.split('_').pop();
        var targetInputId = '#appointment_document_url_' + appointmentId;

        var mediaUploader = wp.media({
            title: 'Select Document',
            button: { text: 'Use this file' },
            multiple: false
        });

        mediaUploader.open();
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            console.log('Selected File URL:', attachment.url);  // Add this line to debug
            $(targetInputId).val(attachment.url);
        });
    });
});
