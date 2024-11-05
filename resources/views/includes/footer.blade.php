<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <!-- First Column -->
            <div class="col-12 col-sm-6 text-left">
                <span class="text-muted d-inline-block">{{ $app->copy_right_statement ?? 'Copyright@___' }}</span>
                <!-- Copyright content -->
            </div>

            <!-- Second Column -->
            <div class="col-12 col-sm-6 text-right">
                <span class="text-muted d-inline-block font-weight-bold">Env: <strong class="text-behance">{{ $app->environment ?? 'Not set' }}</strong></span>,
                <span class="text-muted d-inline-block">App: <strong>{{ $app->app_name ?? 'Noclick' }}</strong></span>,
                <span class="text-muted d-inline-block"> Version: <strong>{{ $app->app_version ?? '2.0.1' }}</strong></span>
                <br>
                <span class="text-muted d-inline-block">{{ $app->email ?? 'test@test.com' }}</span>,
                <span class="text-muted d-inline-block">{{ $app->phone ?? '01234567890'}}</span>
                <!-- Copyright content -->
            </div>
        </div>
    </div>
</footer>
