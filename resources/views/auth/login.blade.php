@extends('layouts.app')

@section('content')

<div class="container">

{{--    <h1>{{ config('app.name', 'Laravel') }}</h1>--}}


    <h1 class="oneclick-noclick">
        <div>
            <!-- Use classes 2,3,4, or 5 to match the number of words -->
            <ul class="flip4">
                <li>Noclick</li>
                <li>&</li>
                <li>Oneclick</li>
                <li>Welcome</li>
            </ul>
        </div>
    </h1>

    <section class="login">
        <form class="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">
                <input id="email"
                       type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       name="email"
                       placeholder="Enter email address"
                       value="{{ old('email') }}" required autocomplete="email" autofocus>
                </label>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    <input id="password"
                           type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password"
                           placeholder="Enter your password"
                           required autocomplete="current-password"
                    >
                </label>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="login-btn">
                <button type="submit">
                    {{ __('Login') }}
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
