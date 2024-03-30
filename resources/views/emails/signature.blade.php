@section('signature')
    @if($template['signature'])
        @include('includes.signature')
    @else
        @include('includes.inline-signature')
    @endif
@endsection
