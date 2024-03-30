<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body>
    <!-- This warning is used for test purposes. -->
    @yield('warning')

    <!-- Mail message -->
    <table style="border-collapse: collapse; font-family: 'Times New Roman', Times, serif; width: 100%; font-size: 15px;">
        <!-- Greeting -->
        <tr><td><b>@yield('greeting')</b></td></tr>

        <!-- Message -->
        <tr><td style="padding-top: 15px; padding-bottom: 25px;">@yield('message') <b>@yield('date')</b></td></tr>
    </table>

    <!-- Add content more -->
    @yield('content')

    <!-- Add signature -->
    @yield('signature')



    <!-- Warning: If you are using one of the signatures below for every mail template, please comment out the signature used in other files except the master file. -->
{{--    <!-- If you are using inline signature, please uncomment the line below. -->--}}
{{--    @include('includes.inline-signature')--}}


{{--    <!-- If you are using user signature, please uncomment the line below. -->--}}
{{--    @include('includes.signature')--}}


</body>
</html>

