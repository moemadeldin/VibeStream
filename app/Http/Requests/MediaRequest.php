<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post && $this->user()->id === $post->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'media_type' => ['required', 'integer', Rule::in(array_map(fn ($case) => $case->value, MediaType::cases()))],
            'media' => ['required', 'file', 'mimes:png,jpg,jpeg,mp4,mov', 'max:20840'],
        ];
    }
}
