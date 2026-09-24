<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    /**
     * Configure extra validation for the reply target.
     */
    public function after(): array
    {
        return [
            function (): void {
                $post = $this->route('post');
                $parent = $this->route('comment');

                if (! $parent instanceof Comment) {
                    return;
                }

                if ($parent->post_id !== $post->id) {
                    $this->validator->errors()->add(
                        'parent_id',
                        'You can only reply to comments on this post.'
                    );

                    return;
                }

                if ($parent->depth >= Comment::MAX_NESTING_DEPTH) {
                    $this->validator->errors()->add(
                        'parent_id',
                        'Reply threads can only be '.Comment::MAX_NESTING_DEPTH.' levels deep.'
                    );
                }
            },
        ];
    }
}
