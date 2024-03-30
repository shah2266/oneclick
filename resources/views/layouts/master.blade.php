<!doctype html>
<html lang="en">

<head>
    @include('includes.meta-info')

    <title>Oneclick - @yield('title') </title>

    @include('includes.css-links')

</head>

<body class="sidebar-fixed">

<div class="container-scroller">

    @include('includes.sidebar')

    <!-- partial -->
    <div class="container-fluid page-body-wrapper">

        <!-- partial:partials/_navbar.html -->
        @include('includes.navbar')

        <!-- partial -->
        <div class="main-panel">
            <div class="content-wrapper">

                @yield('content')

            </div>
            <!-- content-wrapper ends -->

            <!-- partial:partials/_footer.html -->
            @include('includes.footer')
            <!-- partial -->

        </div>
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- plugins:js -->
@include('includes.footer-script')

</body>

</html>
