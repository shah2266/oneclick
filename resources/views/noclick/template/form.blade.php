@csrf

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="template_name">Template name:<code>*</code></label>
        <input
            type="text"
            class="form-control {{ $errors->has('template_name') ? ' has-error':'' }}"
            name="template_name" id="template_name"
            value="{{old('template_name') ?? $template->template_name }}"
            placeholder="Template name"
        >
        @if ($errors->has('template_name'))
            <span class="error-message" role="alert">
                {{ $errors->first('template_name') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="to_email_addresses">To Email Addresses (comma-separated):<code>*</code></label>
        <textarea name="to_email_addresses" id="to_email_addresses" cols="30" rows="3" class="form-control {{ $errors->has('to_email_addresses') ? ' has-error':'' }}"
                  placeholder="Enter email addresses separated by commas"
        >{{old('to_email_addresses') ?? $template->to_email_addresses}}</textarea>
        @if ($errors->has('to_email_addresses'))
            <span class="error-message" role="alert">
               {{ $errors->first('to_email_addresses') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="cc_email_addresses">CC Email Addresses (comma-separated):<code>*</code></label>
        <textarea name="cc_email_addresses" id="cc_email_addresses" cols="30" rows="3" class="form-control {{ $errors->has('cc_email_addresses') ? ' has-error':'' }}" placeholder="Enter email addresses separated by commas">{{old('cc_email_addresses') ?? $template->cc_email_addresses}}</textarea>
        @if ($errors->has('cc_email_addresses'))
            <span class="error-message" role="alert">
               {{ $errors->first('cc_email_addresses') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="subject">Subject: <code>*</code></label>
        <input
            type="text"
            class="form-control {{ $errors->has('subject') ? ' has-error':'' }}"
            name="subject" id="subject"
            value="{{old('subject') ?? $template->subject}}"
            placeholder="Subject"
        >
        @if ($errors->has('subject'))
            <span class="error-message" role="alert">
                {{ $errors->first('subject') }}
            </span>
        @endif
    </div>

    <div class="col-md-6 col-sm-12">
        <label for="has_subject_date">Date position in subject: <code>*</code></label>
        <select class="form-control" name="has_subject_date" id="has_subject_date" required>
            <option value="" disabled>--- Select date position ---</option>
            @foreach($template->inlineDateHasInSubject() as $key => $value)
                <option value="{{ $key }}"
                    {{ (old('has_subject_date') ?? $template->has_subject_date) == $value ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('has_subject_date'))
            <span class="error-message" role="alert">
                {{ $errors->first('has_subject_date') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="greeting">Greeting: (Optional)</label>
        <input
            type="text"
            class="form-control {{ $errors->has('greeting') ? ' has-error':'' }}"
            name="greeting" id="greeting"
            value="{{old('greeting') ?? $template->greeting}}"
            placeholder="Enter greeting. e.g: Dear John Doe, Dear Concern, Dear Sir, etc."
        >
        @if ($errors->has('greeting'))
            <span class="error-message" role="alert">
               {{ $errors->first('greeting') }}
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="mail_body_content">Mail body content:(Optional)</label>
        <textarea name="mail_body_content" id="mail_body_content" cols="30" rows="4" class="form-control {{ $errors->has('mail_body_content') ? ' has-error':'' }}" placeholder="Enter mail body content">{{old('mail_body_content') ?? $template->mail_body_content}}</textarea>
        @if ($errors->has('mail_body_content'))
            <span class="error-message" role="alert">
               {{ $errors->first('mail_body_content') }}
            </span>
        @endif
    </div>

    <div class="col-md-6 col-sm-12">
        <label for="has_inline_date">Has inline date with mail body content:<code>*</code></label>
        <select class="form-control" name="has_inline_date" id="has_inline_date" required>
            <option value="" disabled>--- Select has inline date ---</option>
            @foreach($template->inlineDateHasInBodyContent() as $key => $value)
                <option value="{{ $key }}"
                    {{ (old('has_inline_date') ?? $template->has_inline_date) == $value ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}">
</div>


<div class="form-group row">
    <div class="col">
        <label for="has_custom_mail_template">Has custom mail template: (Optional)</label>
        <select class="form-control" name="has_custom_mail_template" id="has_custom_mail_template">
            <option value="" disabled>--- Select custom mail template ---</option>
            @foreach($existingTemplates as $name)
                @php
                    $lowercaseName = strtolower(str_replace('-', '_', $name));
                    $isSelected = (old('has_custom_mail_template') === $lowercaseName) ||
                                  ($template->has_custom_mail_template && $lowercaseName === $template->has_custom_mail_template) ||
                                  (strpos($name, "Default") !== false && !$template->has_custom_mail_template);
                @endphp
                <option value="{{ $lowercaseName }}" {{ $isSelected ? 'selected' : '' }} >
                    {{ $name }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('has_custom_mail_template'))
            <span class="error-message" role="alert">
           {{ $errors->first('has_custom_mail_template') }}
        </span>
        @endif
    </div>

    <div class="col">
        <label for="signature">Signature:<code>*</code></label>
        <select class="form-control" name="signature" id="signature">
            <option value="" disabled>--- Select signature ---</option>
            @foreach($template->signatures() as $key => $value)
                @if (old('signature') == $key)
                    <option value="{{ $key }}" selected >{{ $value }}</option>
                @else
                    <option value="{{ $key }}" {{ $template->signature == $value ? 'selected' : '' }}>{{ $value }}</option>
                @endif
            @endforeach
        </select>
        @if ($errors->has('signature'))
            <span class="error-message" role="alert">
               {{ $errors->first('signature') }}
            </span>
        @endif
    </div>

</div>

<div class="form-group row">
    <div class="col-md-6 col-sm-12">
        <label for="status">Status:</label>
        <select class="form-control" name="status" id="status">
            <option value="" disabled>--- Select status ---</option>
            @foreach($template->statusOptions() as $key => $value)
                <option value="{{ $key }}"
                    {{ (old('status') ?? $template->status) == $value ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @if ($errors->has('status'))
            <span class="error-message" role="alert">
               {{ $errors->first('status') }}
            </span>
        @endif
    </div>
</div>



