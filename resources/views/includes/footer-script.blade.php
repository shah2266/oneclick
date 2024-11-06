<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<!-- end-inject -->
<!-- Plugin js for this page -->
<script src="{{ asset('assets/vendors/select2/select2.min.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
<script src="{{ asset('assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
<script src="{{ asset('assets/vendors/owl-carousel-2/owl.carousel.min.js') }}"></script>
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>
<script src="{{ asset('assets/js/settings.js') }}"></script>
<script src="{{ asset('assets/js/todolist.js') }}"></script>
<script src="{{ asset('assets/js/file-upload.js') }}"></script>
<!-- end-inject -->

<!-- Custom js for this page -->
<script src="{{ asset('assets/js/dashboard.js') }}"></script>
<!-- End custom js for this page -->
<script src="{{ asset('assets/js/select2.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datepicker.js') }}"></script>
<!-- Add this script at the end of your HTML body or in a separate script file -->

<script src="{{ asset('assets/js/ping.js') }}"></script>
<script src="{{ asset('assets/js/holiday.js') }}"></script>
<script src="{{ asset('assets/js/frequency.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#theme-toggle').click(function() {
            // Send AJAX request to toggle theme preference
            $.ajax({
                type: 'POST',
                url: '{{ route("toggleTheme") }}',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Handle success response if needed
                    console.log(response.message);
                    // Reload the page in the background
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // Handle error response if needed
                    console.error(error);
                }
            });
        });

        // Server ping
        @isset($servers)
            const servers = @json($servers);
            servers.forEach(function(server) {
                pingServer(server.IPAddress);
            });
        @endisset

    });

    // Function to toggle status for commands
    function toggleCommandStatus(commandId, isChecked) {
        toggleStatus("command", commandId, isChecked, "/noclick/commands/");
    }

    // Function to toggle status for schedules
    function toggleScheduleStatus(scheduleId, isChecked) {
        toggleStatus("schedule", scheduleId, isChecked, "/noclick/schedules/");
    }

    function toggleStatus(targetName, id, isChecked, url) {
        // Select the toggle button once
        const $toggle = $('#toggle-'+ targetName + '-' + id);

        // Make an AJAX request to toggle the status
        $.ajax({
            url: url + id + "/toggle-status",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: isChecked ? 'on' : 'off'
            },
            success: function(response) {
                // Update the button text with the new status using the stored selector
                $toggle.prop('checked', response.status === 'on');
                $toggle.next('.custom-control-label').text(response.status);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function pollForUpdates(targetName, id, url) {
        // Make an AJAX request to check for updates
        //alert(id);
        $.ajax({
            url: url +"/updates",
            type: 'GET',
            success: function(response) {
                // Update the switch toggle buttons with the new status for each command
                $.each(response, function(id, status) {
                    const $toggle = $('#toggle-' + targetName + '-' + id); // Select once
                    $toggle.prop('checked', status === 'on');
                    $toggle.next('.custom-control-label').text(status);
                });
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            },
            complete: function() {
                // Poll for updates again after a delay
                setTimeout(pollForUpdates, 1000); // Poll every 1 seconds
            }
        });
    }
</script>





