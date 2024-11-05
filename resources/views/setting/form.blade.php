@csrf

<div class="form-group row">
    <div class="col-6">
        <label id="file">File upload:</label>
        <input type="file" name="logo" class="file-upload-default">
        <div class="input-group col-xs-12">
            <input type="text" class="form-control file-upload-info" disabled placeholder="Upload logo">
            <span class="input-group-append">
            <button class="file-upload-browse btn btn-primary" type="button">Upload</button>
          </span>
        </div>
    </div>
    <div class="col-6">
        <label for="environment">Environment:<code>*</code></label>
        <select class="form-control select2 {{ $errors->has('environment') ? ' has-error':'' }}" name="environment" id="environment" required>
            <option value="">--- Select environment ---</option>
            @foreach ($setting->environmentOptions() as $key=>$environment)
                <option value="{{$key}}" {{ $setting->environment == $environment ? 'Selected':''}} >{{$environment}}</option>
            @endforeach
        </select>
        @if ($errors->has('environment'))
            <span class="error-message" role="alert">
               {{ $errors->first('environment') }}
            </span>
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col">
        <label for="app_name">App name:<code>*</code></label>
        <input type="text"
               class="form-control {{ $errors->has('app_name') ? ' has-error':'' }}"
               name="app_name" id="app_name" value="{{old('app_name') ?? $setting->app_name}}"
               placeholder="Enter app name">
        @if ($errors->has('app_name'))
            <span class="error-message" role="alert">
               {{ $errors->first('app_name') }}
            </span>
        @endif
    </div>

    <div class="col">
        <label for="short_name">Short name:<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('short_name') ? ' has-error':'' }}" name="short_name" id="short_name"
               value="{{old('short_name') ?? $setting->short_name}}" placeholder="Short name">
        @if ($errors->has('short_name'))
            <span class="error-message" role="alert">
               {{ $errors->first('short_name') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="app_version">App version (Optional):</label>
        <input type="text" class="form-control {{ $errors->has('app_version') ? ' has-error':'' }}" name="app_version" id="app_version"
               value="{{old('app_version') ?? $setting->app_version }}" placeholder="Enter app version">
        @if ($errors->has('app_version'))
            <span class="error-message" role="alert">
               {{ $errors->first('app_version') }}
            </span>
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col">
        <label for="email">Email (Optional):</label>
        <input type="email" class="form-control {{ $errors->has('email') ? ' has-error':'' }}" name="email" id="email"
               value="{{old('email') ?? $setting->email}}" placeholder="Enter email">
        @if ($errors->has('email'))
            <span class="error-message" role="alert">
               {{ $errors->first('email') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="phone">Phone (Optional):</label>
        <input type="text" class="form-control {{ $errors->has('phone') ? ' has-error':'' }}" name="phone" id="phone"
               value="{{old('phone') ?? $setting->phone}}" placeholder="Enter phone">
        @if ($errors->has('phone'))
            <span class="error-message" role="alert">
               {{ $errors->first('phone') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="address">Address (Optional):</label>
        <input type="text" class="form-control {{ $errors->has('address') ? ' has-error':'' }}" name="address" id="address"
               value="{{old('address') ?? $setting->address}}" placeholder="Enter address">
        @if ($errors->has('address'))
            <span class="error-message" role="alert">
               {{ $errors->first('address') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="url">URL (Optional):</label>
        <input type="text" class="form-control {{ $errors->has('email') ? ' has-error':'' }}" name="url" id="url"
               value="{{old('url') ?? $setting->url}}" placeholder="Enter url">
        @if ($errors->has('url'))
            <span class="error-message" role="alert">
               {{ $errors->first('url') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="copy_right_statement">Copy right statement: <code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('copy_right_statement') ? ' has-error':'' }}" name="copy_right_statement" id="copy_right_statement"
               value="{{old('copy_right_statement') ?? $setting->copy_right_statement}}" placeholder="Enter copy right statement">
        @if ($errors->has('copy_right_statement'))
            <span class="error-message" role="alert">
               {{ $errors->first('copy_right_statement') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="status">Status:<code>*</code></label>
        <select class="form-control select2 {{ $errors->has('status') ? ' has-error':'' }}" name="status" id="status" required>
            <option value="">--- Select status ---</option>
            @foreach ($setting->statusOptions() as $key=>$status)
                <option value="{{$key}}" {{ $setting->status == $status ? 'Selected':''}} >{{$status}}</option>
            @endforeach
        </select>
        @if ($errors->has('status'))
            <span class="error-message" role="alert">
               {{ $errors->first('status') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">
    <div class="col">
        <label for="editor1">Description:</label>
        <textarea name="description" id="editor1" cols="30" rows="3"
                  class="form-control {{ $errors->has('description') ? ' has-error':'' }}"
                  placeholder="description">{{old('description') ?? Str::of($setting->description)->trim()}}
        </textarea>
        @if ($errors->has('description'))
            <span class="error-message" role="alert">
               {{ $errors->first('description') }}
            </span>
        @endif
    </div>
    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
</div>
