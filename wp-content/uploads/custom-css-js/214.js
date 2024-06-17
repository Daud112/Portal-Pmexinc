<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#wpcf7-f199-p63-o1').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // Example AJAX request (adjust as per your form submission handling)
        $.ajax({
            type: 'POST',
            url: ajaxurl, // Replace with your AJAX handler URL or endpoint
            data: $('#wpcf7-f199-p63-o1').serialize(), // Serialize form data
            success: function(response) {
                // Handle success response if needed
                $('#patient-btn').show(); // Show the submit button
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    });
});
</script>
<!-- end Simple Custom CSS and JS -->
