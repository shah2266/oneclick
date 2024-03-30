@csrf
<div class="form-group row">
    <div class="col">
        <label for="company">Select Company Type</label>
        <select class="form-control {{ $errors->has('type') ? ' has-error':'' }}" name="type" id="company">
            <option>Select Company Type</option>
            @foreach ($company->typeOptions() as $typeKey=>$type)
                <option value="{{$typeKey}}" {{ $company->type == $type ? 'Selected':''}}>{{$type}}</option>
            @endforeach
        </select>
        @if ($errors->has('type'))
            <span class="error-message" role="alert">
               {{ $errors->first('type') }}
            </span>
        @endif
    </div>

    <div class="col">
        <label for="status">Status<code>*</code></label>
        <select class="form-control {{ $errors->has('status') ? ' has-error':'' }}" name="status" id="status">
            @foreach ($company->statusOption() as $typeKey=>$type)
                <option value="{{$typeKey}}" {{ $company->status == $type ? 'Selected':''}} >{{$type}}</option>
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
        <label for="precedence">Company precedence<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('precedence') ? ' has-error':'' }}" name="precedence" id="precedence" value="{{old('precedence') ?? $company->precedence}}" placeholder="Company precedence">
        @if ($errors->has('precedence'))
            <span class="error-message" role="alert">
                {{ $errors->first('precedence') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="systemId">System uses id <code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('systemId') ? ' has-error':'' }}" name="systemId" id="systemId" value="{{old('systemId') ?? $company->systemId}}" placeholder="System id">
        @if ($errors->has('systemId'))
            <span class="error-message" role="alert">
               {{ $errors->first('systemId') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="fullName">Full name<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('fullName') ? ' has-error':'' }}" name="fullName" id="fullName" value="{{old('fullName') ?? $company->fullName}}" placeholder="Full name">
        @if ($errors->has('fullName'))
            <span class="error-message" role="alert">
               {{ $errors->first('fullName') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="shortName">Short name <code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('shortName') ? ' has-error':'' }}" name="shortName" id="shortName" value="{{old('shortName') ?? $company->shortName}}" placeholder="Short name">
        @if ($errors->has('shortName'))
            <span class="error-message" role="alert">
               {{ $errors->first('shortName') }}
            </span>
        @endif
    </div>

    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
</div>
