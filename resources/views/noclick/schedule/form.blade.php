@csrf

<div class="form-group row">
    <div class="col">
        <!-- This is frequency options.  -->
        <label for="frequency">Frequency:<code>*</code></label>
        <select class="form-control" name="frequency" id="frequency">
            <option value="">--- Select frequency ---</option>
            @foreach($schedule->frequencyOptions() as $key => $value)
                <option value="{{ $key }}"
                    {{ (string) old('frequency', $schedule->frequency) === (string) $key ? 'selected' : '' }}>
                    {{ ucfirst($value) }}
                </option>
            @endforeach
        </select>

        @if ($errors->has('frequency'))
            <span class="error-message" role="alert">
                {{ $errors->first('frequency') }}
            </span>
        @endif
    </div>

    <div class="col">
        <label for="command_id">Command:<code>*</code></label>
        <select class="form-control select2 " name="command_id" id="command_id">
            <option value="">--- Select command ---</option>
            @foreach($commands as $data)
                <option value="{{ $data->id }}"
                    {{ (old('command_id') ?? $schedule->command_id) == $data->id ? 'Selected':''}} >
                    {{ $data->command }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('command_id'))
            <span class="error-message" role="alert">
               {{ $errors->first('command_id') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">
    <div class="col">
        <label for="time">Time: <code>*</code></label>
        <input
            type="time"
            class="form-control {{ $errors->has('time') ? ' has-error':'' }}"
            name="time" id="time"
            value="{{old('time') ?? $schedule->time}}"
            placeholder="time"
        >
        @if ($errors->has('time'))
            <span class="error-message" role="alert">
               {{ $errors->first('time') }}
            </span>
        @endif
    </div>

    <div class="col">
        <div class="form-group">

            <label for="days">Select days: (Optional) </label>
            <select id="days" name="days[]" class="js-example-basic-multiple" multiple="multiple" style="width:100%">
                <option value="" disabled>--- Select days ---</option>
                @foreach($dayNames as $dayName)
                    <option value="{{ strtolower($dayName) }}" {{ in_array(strtolower($dayName), old('days', explode(',', $schedule->days))) ? 'selected' : '' }}>
                        {{ $dayName }}
                    </option>
                @endforeach
            </select>
            @if ($errors->has('days'))
                <span class="error-message" role="alert">
               {{ $errors->first('days') }}
            </span>
            @endif
        </div>
    </div>

{{--    <div class="col">--}}
{{--        <label for="holiday">Holidays: (Optional) </label>--}}
{{--        <input--}}
{{--            type="text"--}}
{{--            class="form-control multi_date {{ $errors->has('holiday') ? ' has-error':'' }}"--}}
{{--            name="holiday" id="holiday"--}}
{{--            value="{{old('holiday') ?? $schedule->holiday}}"--}}
{{--            placeholder="Holidays separated by commas.e.g: 14-Feb-2024"--}}
{{--        >--}}
{{--        @if ($errors->has('holiday'))--}}
{{--            <span class="error-message" role="alert">--}}
{{--               {{ $errors->first('holiday') }}--}}
{{--            </span>--}}
{{--        @endif--}}

{{--    </div>--}}

</div>

<div class="form-group row">

    <div class="col-6">
        <label for="status">Status<code>*</code></label>
        <select class="form-control select2 " name="status" id="status">
            @foreach($schedule->statusOptions() as $key => $value)
                <option value="{{ $key }}"
                    {{ (string) old('status', $schedule->status) === (string) $key ? 'selected' : '' }}>
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


