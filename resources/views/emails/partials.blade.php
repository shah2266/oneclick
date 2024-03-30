@php use Carbon\Carbon; @endphp
<!-- HTML title -->
@section('title')
    @isset($template['subject'])
        {{ $template['subject'] }}
    @endisset
@endsection

<!-- Warning message -->
{{--@section('warning')--}}
{{--    <h3 style="color:#ec1100;">[Ignore this email; it is an auto-generated test email.!!]</h3>--}}
{{--@endsection--}}

<!-- Greeting -->
@section('greeting')
    @isset($template['greeting'])
        {{ $template['greeting'] }}
    @endisset
@endsection

<!-- Message -->
{{--@section('message', 'This is test mail. Testing date at: ')--}}
@section('message')
    @isset($template['mail_body_content'])
        {!! $template['mail_body_content'] !!}
    @endisset
@endsection

<!-- If date used -->
@section('date')
    @if(strtolower($template['has_inline_date']) === 'yes')
        {{ ' ' . Carbon::yesterday()->format('d-M-Y') . '.' }}
    @endif
@endsection
