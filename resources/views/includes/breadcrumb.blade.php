<!-- breadcrumb -->
<div class="page-header">
    <h3 class="page-title"> @yield('section-title') </h3>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/home') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">@yield('currentPage')</li>
        </ol>
    </nav>
</div>
