(function($) {
    // Define the updateDate function in the global scope
    window.updateDate = function(holiday) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Make an AJAX request to update the database
        $.ajax({
            url: "/noclick/schedules/set/holiday",
            type: "POST",
            data: {
                _token: csrfToken,
                holiday: holiday
            },
            success: function() {
                // Handle success response
                //console.log(response);
                // Update holiday information for each record with frequency 2
                $('.holiday-display').text(holiday); // Update the holiday display with the new value
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error(xhr.responseText);
            }
        });
    };

    // Attach the onchange event handler
    $('#holiday').on('change', function() {
        const holiday = $(this).val();
        if (holiday) {
            // Call the updateDate function
            updateDate(holiday);
        }
    });
})(jQuery);
