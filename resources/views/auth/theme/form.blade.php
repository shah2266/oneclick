@csrf

<div class="form-group row">
    <div class="col-md-6 col-sm-12">

        <!--Username-->
        <div class="form-group">
            <label for="theme_name">Theme name:<code>*</code></label>
            <input
                type="text"
                class="form-control {{ $errors->has('theme_name') ? ' has-error':'' }}"
                name="theme_name" id="theme_name"
                value="{{old('theme_name') ?? $theme->theme_name }}"
                placeholder="Enter theme name"
            >
            @if ($errors->has('theme_name'))
                <span class="error-message" role="alert">
                {{ $errors->first('theme_name') }}
            </span>
            @endif
        </div>
        <!--#Username-->

        <!--Contact number-->
        <div class="form-group">
            <label for="stylesheet_name">Stylesheet name: <code>*</code></label>
            <input
                type="text"
                class="form-control {{ $errors->has('stylesheet_name') ? ' has-error':'' }}"
                name="stylesheet_name" id="stylesheet_name"
                value="{{old('stylesheet_name') ?? $theme->stylesheet_name}}"
                placeholder="Enter stylesheet name"
            >
            @if ($errors->has('stylesheet_name'))
                <span class="error-message" role="alert">
               {{ $errors->first('stylesheet_name') }}
            </span>
            @endif
        </div>
        <!--#Contact number-->

{{--        @if(\Route::currentRouteName() === 'themes.edit')--}}
{{--        <div class="form-group">--}}
{{--            <label for="user_id">Assign theme:<code>*</code></label>--}}
{{--            <select class="form-control select2 {{ $errors->has('user_id') ? ' has-error':'' }}" name="user_id" id="user_id">--}}
{{--                <option value="">---Select user---</option>--}}
{{--                @foreach ($users as $key=>$user)--}}
{{--                    <option value="{{ $user->id }}" {{ $theme->user_id == $user->id ? 'Selected':''}} >{{$user->name}}</option>--}}
{{--                @endforeach--}}
{{--            </select>--}}
{{--            @if ($errors->has('user_id'))--}}
{{--                <span class="error-message" role="alert">--}}
{{--               {{ $errors->first('user_id') }}--}}
{{--            </span>--}}
{{--            @endif--}}
{{--        </div>--}}
{{--        @endif--}}

    </div>
</div>



