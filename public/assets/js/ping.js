function pingServer(ip) {
    // const csrfToken = '{{ csrf_token() }}';
    const csrfToken = document.getElementById('csrf-token').value;
    const ipaddress = ip.replace(/\./g, "_");
    const idSelector = "#" + ipaddress + "_output";

    $.ajax({
        url: "/server/connectivity/ping",
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: { ip: ip },
        beforeSend: function() {
            // Show progress bar before sending the request
            $(idSelector).html('<div class="progress">' +
                '<div class="progress-bar progress-bar-striped progress-bar-animated text-left" role="progressbar" style="width: 100%">' +
                '<span class="p-1">IP Address: ' + ip + ' ping starting..</span>' +
                '</div></div>');
        },
        success: function(response) {
            if (response && response.ip && response.output) {
                const output = response.output.join('<br>');

                // Check if "Request timed out." appears in the output
                if (response.output.includes("Request timed out.")) {
                    $(idSelector).html('<div class="bg-danger text-white d-flex d-md-block d-xl-flex flex-row py-3 px-4 px-md-3 px-xl-4 rounded mt-3">' + output + "</div>");
                } else {
                    $(idSelector).html('<div class="bg-gray-dark text-success d-flex d-md-block d-xl-flex flex-row py-3 px-4 px-md-3 px-xl-4 rounded mt-3">' + output + "</div>");
                }
            } else {
                console.error('Invalid response format:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error in AJAX request:', error);
        },
        complete: function() {
            // Hide progress bar when the request is complete
            $(idSelector + ' .progress').remove();
        }
    });
}
