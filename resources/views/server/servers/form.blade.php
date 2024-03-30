@csrf
<div class="form-group row">
    <div class="col">
        <label for="machineName">Machine name<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('machineName') ? ' has-error':'' }}" name="machineName" id="machineName" value="{{old('machineName') ?? $info->machineName}}" placeholder="Machine name" required>
        @if ($errors->has('machineName'))
            <span class="error-message" role="alert">
                {{ $errors->first('machineName') }}
            </span>
        @endif
    </div>

    <div class="col">
        <label for="shortName">Short name</label>
        <input type="text" class="form-control {{ $errors->has('shortName') ? ' has-error':'' }}" name="shortName" id="shortName" value="{{old('shortName') ?? $info->shortName}}" placeholder="Short name">
        @if ($errors->has('shortName'))
            <span class="error-message" role="alert">
               {{ $errors->first('shortName') }}
            </span>
        @endif
    </div>
</div>
<div class="form-group row">
    <div class="col">
        <label for="ipAddress">IP address<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('ipAddress') ? ' has-error':'' }}" name="ipAddress" id="ipAddress" value="{{old('ipAddress') ?? $info->ipAddress}}" placeholder="IP address" required>
        @if ($errors->has('ipAddress'))
            <span class="error-message" role="alert">
               {{ $errors->first('ipAddress') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="operatingSystem">Operating system<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('operatingSystem') ? ' has-error':'' }}" name="operatingSystem" id="operatingSystem" value="{{old('operatingSystem') ?? $info->operatingSystem}}" placeholder="Operating system" required>
        @if ($errors->has('operatingSystem'))
            <span class="error-message" role="alert">
               {{ $errors->first('operatingSystem') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="manufacturer">Manufacturer<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('manufacturer') ? ' has-error':'' }}" name="manufacturer" id="manufacturer" value="{{old('manufacturer') ?? $info->manufacturer}}" placeholder="Manufacturer" required>
        @if ($errors->has('manufacturer'))
            <span class="error-message" role="alert">
               {{ $errors->first('manufacturer') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="model">Model<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('model') ? ' has-error':'' }}" name="model" id="model" value="{{old('model') ?? $info->model}}" placeholder="Model" required>
        @if ($errors->has('model'))
            <span class="error-message" role="alert">
               {{ $errors->first('model') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="processor">Processor</label>
        <input type="text" class="form-control {{ $errors->has('processor') ? ' has-error':'' }}" name="processor" id="processor" value="{{old('processor') ?? $info->processor}}" placeholder="Processor">
        @if ($errors->has('processor'))
            <span class="error-message" role="alert">
               {{ $errors->first('processor') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="hdd">HDD<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('hdd') ? ' has-error':'' }}" name="hdd" id="hdd" value="{{old('hdd') ?? $info->hdd}}" placeholder="HDD Size" required>
        @if ($errors->has('hdd'))
            <span class="error-message" role="alert">
               {{ $errors->first('hdd') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="memoryRam">Memory ram<code>*</code></label>
        <input type="text" class="form-control {{ $errors->has('memoryRam') ? ' has-error':'' }}" name="memoryRam" id="memoryRam" value="{{old('memoryRam') ?? $info->memoryRam}}" placeholder="Memory ram">
        @if ($errors->has('memoryRam'))
            <span class="error-message" role="alert">
               {{ $errors->first('memoryRam') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="osMemory">OS memory</label>
        <input type="text" class="form-control {{ $errors->has('osMemory') ? ' has-error':'' }}" name="osMemory" id="osMemory" value="{{old('osMemory') ?? $info->osMemory}}" placeholder="OS memory">
        @if ($errors->has('osMemory'))
            <span class="error-message" role="alert">
               {{ $errors->first('osMemory') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col">
        <label for="applicationRunning">Application running</label>
        <input type="text" class="form-control {{ $errors->has('applicationRunning') ? ' has-error':'' }}" name="applicationRunning" id="applicationRunning" value="{{old('applicationRunning') ?? $info->applicationRunning}}" placeholder="Application running">
        @if ($errors->has('applicationRunning'))
            <span class="error-message" role="alert">
               {{ $errors->first('applicationRunning') }}
            </span>
        @endif
    </div>
    <div class="col">
        <label for="status">Status<code>*</code></label>
        <select class="form-control select2 {{ $errors->has('status') ? ' has-error':'' }}" name="status" id="status" required>
            @foreach ($info->statusOption() as $key=>$status)
                <option value="{{$key}}" {{ $info->status == $status ? 'Selected':''}} >{{$status}}</option>
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
        <label for="editor1">Comment</label>
        <textarea name="comment" id="editor1" cols="30" rows="3" class="form-control {{ $errors->has('comment') ? ' has-error':'' }}" placeholder="Comment">{{old('comment') ?? $info->comment}}</textarea>
        @if ($errors->has('comment'))
            <span class="error-message" role="alert">
               {{ $errors->first('comment') }}
            </span>
        @endif
    </div>
    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
</div>
