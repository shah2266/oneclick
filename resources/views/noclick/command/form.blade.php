@csrf

<div class="form-group row">
    <div class="col">
        <label for="name">Name:<code>*</code></label>
        <input
            type="text"
            class="form-control {{ $errors->has('name') ? ' has-error':'' }}"
            name="name" id="name"
            value="{{old('name') ?? $command->name}}"
            placeholder="Name"
        >
        @if ($errors->has('name'))
            <span class="error-message" role="alert">
                {{ $errors->first('name') }}
            </span>
        @endif
    </div>

    <div class="col">
        <label for="command">Command:<code>*</code></label>
        <input
            type="text"
            class="form-control {{ $errors->has('command') ? ' has-error':'' }}"
            name="command" id="command"
            value="{{old('command') ?? $command->command}}"
            placeholder="Command"
        >
        @if ($errors->has('command'))
            <span class="error-message" role="alert">
               {{ $errors->first('command') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">

    <div class="col">
        <label for="mail_template_id">Mail template<code>*</code></label>
        <select class="form-control select2 " name="mail_template_id" id="mail_template_id">
            <option value="">--- Select mail template ---</option>
            @foreach($mailTemplates as $data)
                <option value="{{ $data->id }}"
                    {{ (old('mail_template_id') ?? $command->mail_template_id) == $data->id ? 'Selected':''}} >
                    {{ $data->template_name }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('mail_template_id'))
            <span class="error-message" role="alert">
               {{ $errors->first('mail_template_id') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">

    <div class="col-6">
        <label for="status">Status<code>*</code></label>
        <select class="form-control select2 " name="status" id="status">
            @foreach($command->statusOptions() as $key => $value)
                <option value="{{ $key }}"
                    {{ (string) old('status', $command->status) === (string) $key ? 'selected' : '' }}>
                    {{ ucfirst($value) }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('status'))
            <span class="error-message" role="alert">
               {{ $errors->first('status') }}
            </span>
        @endif
    </div>
    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
</div>


