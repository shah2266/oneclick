@csrf

<div class="form-group row">
    <div class="col-md-6 col-sm-12">

        <!--image-->
        <div class="form-group">
            <label id="file">File upload:</label>
            <input type="file" name="image" class="file-upload-default">
            <div class="input-group col-xs-12">
                <input type="text" class="form-control file-upload-info" disabled placeholder="Upload Image">
                <span class="input-group-append">
                <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
              </span>
            </div>
        </div>
        <!--#image-->

        <!--Username-->
        <div class="form-group">
            <label for="name">User name:<code>*</code></label>
            <input
                type="text"
                class="form-control {{ $errors->has('name') ? ' has-error':'' }}"
                name="name" id="name"
                value="{{old('name') ?? $user->name }}"
                placeholder="Enter user name"
            >
            @if ($errors->has('name'))
                <span class="error-message" role="alert">
                {{ $errors->first('name') }}
            </span>
            @endif
        </div>
        <!--#Username-->

        <!--Email-->
        <div class="form-group">
            <label for="email">Email:<code>*</code></label>
            <input
                type="text"
                class="form-control {{ $errors->has('email') ? ' has-error':'' }}"
                name="email" id="email"
                value="{{old('email') ?? $user->email}}"
                placeholder="Enter email address"
            >
            @if ($errors->has('email'))
                <span class="error-message" role="alert">
                {{ $errors->first('email') }}
            </span>
            @endif
        </div>
        <!--#Email-->

        <!--Contact number-->
        <div class="form-group">
            <label for="contact_number">Contact number: <code>*</code></label>
            <input
                type="text"
                class="form-control {{ $errors->has('contact_number') ? ' has-error':'' }}"
                name="contact_number" id="contact_number"
                value="{{old('contact_number') ?? $user->contact_number}}"
                placeholder="Enter contact number"
            >
            @if ($errors->has('contact_number'))
                <span class="error-message" role="alert">
               {{ $errors->first('contact_number') }}
            </span>
            @endif
        </div>
        <!--#Contact number-->

        <!--Password-->
        <div class="form-group">
            <label for="password">{{ __('Password') }}:</label>
            <input id="password" type="password" class="form-control {{ $errors->has('password') ? ' has-error':'' }}" name="password" required autocomplete="new-password">

            @if ($errors->has('password'))
                <span class="error-message" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
            @endif
        </div>
        <!--#Password-->

        <!--Confirm Password-->
        <div class="form-group">
            <label for="password-confirm">{{ __('Confirm Password') }}:</label>
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
        </div>
        <!--#Confirm Password-->

        <!--Themes-->
        @if(Auth::user()->user_type != 2)
        <div class="form-group">
            <label for="theme_id">Assign theme:</label>
            <select class="form-control select2 {{ $errors->has('theme_id') ? ' has-error':'' }}" name="theme_id" id="theme_id">
                <option value="">---Select theme---</option>
                @foreach ($themes as $key=>$theme)
                    <option value="{{ $theme->id }}" {{ $user->theme_id == $theme->id ? 'Selected':''}} >{{ $theme->theme_name }}</option>
                @endforeach
            </select>
            @if ($errors->has('theme_id'))
                <span class="error-message" role="alert">
               {{ $errors->first('theme_id') }}
            </span>
            @endif
        </div>
        @endif
        <!--#Themes-->

        <!--user_type-->
        <div class="form-group">

            <label for="user_type">User type: <code>*</code></label>
            <select class="form-control select2 " name="user_type" id="user_type">
                <option value="" disabled>--- Select type ---</option>
                @foreach($user->userTypes() as $key => $name)
                    <option value="{{ $key }}"
                        {{ (string) old('user_type', $user->user_type) === (string) $key ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @if ($errors->has('user_type'))
                <span class="error-message" role="alert">
               {{ $errors->first('user_type') }}
            </span>
            @endif
        </div>
        <!--#user_type-->
    </div>

    <div class="col-md-6 col-sm-12 text-center">
        @if(isset($user->image))
            <a href="{{ asset('assets/images/auth/' . $user->image) }}">
                <img src="{{ asset('assets/images/auth/' . $user->image) }}" alt=" {{ __('Missing profile image of ') . $user->name }}">
            </a>
            <figure><em>{{ __('Fig: ') . $user->name }}</em></figure>
        @endif

    </div>

</div>



