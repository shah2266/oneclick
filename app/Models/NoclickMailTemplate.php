<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoclickMailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'to_email_addresses',
        'cc_email_addresses',
        'subject',
        'has_subject_date',
        'greeting',
        'mail_body_content',
        'has_inline_date',
        'has_custom_mail_template',
        'signature',
        'status',
        'user_id'
    ];

    // Default selected value
    protected $attributes = [
        'has_inline_date'   => 1,
        'signature'         => 0,
        'status'            => 1,
        'has_subject_date'  => 0,
    ];

    public function noclickCommand(): BelongsTo
    {
        return $this->belongsTo(NoclickCommand::class, 'id', 'mail_template_id');
    }


    public function getHasInlineDateAttribute($attribute): string
    {
        return $this->inlineDateHasInBodyContent()[$attribute];
    }

    public function getHasSubjectDateAttribute($attribute): string
    {
        return $this->inlineDateHasInSubject()[$attribute];
    }

    public function getSignatureAttribute($attribute): string
    {
        return $this->signatures()[$attribute];
    }

    public function getStatusAttribute($attribute): string
    {
        return $this->statusOptions()[$attribute];
    }

    public function inlineDateHasInBodyContent(): array
    {
        return [
            0 => 'no',
            1 => 'yes'
        ];
    }

    public function inlineDateHasInSubject(): array
    {
        return [
            0 => 'None',
            1 => 'Before subject',
            2 => 'After subject',

        ];
    }

    public function signatures(): array
    {
        return [
            0 => 'Default signature',
            1 => 'Inline signature'
        ];
    }

    public function statusOptions(): array
    {
        return [
            1 => 'Active',
            0 => 'Inactive'
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
