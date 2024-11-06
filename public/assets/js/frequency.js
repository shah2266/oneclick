$(document).ready(function() {
    // Cache the frequency selector and fields in variables
    const $frequency = $('#frequency');
    const $daysField = $('#daysField');
    const $dateField = $('#dateField');
    const $holidayLink = $('#holidayLink');

    // Function to show or hide fields based on selected frequency
    function handleFrequencyChange() {
        const selectedValue = $frequency.val();
        $daysField.hide();
        $dateField.hide();
        $holidayLink.hide();

        if (selectedValue === '0' || selectedValue === '3') {
            // Show days field for Regular or CDR status
            $daysField.show();
        } else if (selectedValue === '1') {
            // Show date input field for Monthly
            $dateField.show();
        } else if (selectedValue === '2') {
            // Show hyperlink for Holiday
            $holidayLink.show();
        }
    }

    // Initial call to set the correct fields based on the default selection
    handleFrequencyChange();

    // Bind change event to frequency select
    $frequency.change(function() {
        handleFrequencyChange();
    });
});
