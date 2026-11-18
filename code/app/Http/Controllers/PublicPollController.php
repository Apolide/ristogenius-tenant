<?php

namespace App\Http\Controllers;

use App\Models\PollInvitation;
use App\Services\Polls\PollDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicPollController extends Controller
{
    public function show(PollInvitation $invitation): View
    {
        app()->setLocale($invitation->language);

        return view('polls.public', ['invitation' => $invitation]);
    }

    public function submit(Request $request, PollInvitation $invitation, PollDelivery $delivery): RedirectResponse
    {
        app()->setLocale($invitation->language);

        DB::transaction(function () use ($request, $invitation, $delivery): void {
            $invitation = PollInvitation::lockForUpdate()->findOrFail($invitation->id);
            if ($invitation->submitted_at) {
                return;
            }

            $rules = [];
            $attributes = [];
            $scores = [];
            foreach ($invitation->snapshot['questions'] as $id => $question) {
                $field = 'answers.'.$id;
                $required = $question['required'] ? 'required' : 'nullable';
                $attributes[$field] = $question['label'];
                $rules[$field] = match ($question['type']) {
                    'rating' => [$required, 'integer', 'between:1,5'],
                    'text' => [$required, 'string', 'max:3000'],
                    'number' => [$required, 'numeric', 'between:-1000000000,1000000000'],
                    'yes_no' => [$required, Rule::in(['yes', 'no'])],
                    'choice' => [$required, 'integer', Rule::in(array_keys($question['choices']))],
                    'checkbox' => [$required, 'array', 'list', 'max:'.count($question['choices'])],
                };
                if ($question['type'] === 'checkbox') {
                    $rules[$field.'.*'] = ['required', 'integer', 'distinct', Rule::in(array_keys($question['choices']))];
                }
            }
            $answers = $request->validate($rules, [], $attributes)['answers'] ?? [];
            $commentRules = [];
            foreach ($invitation->snapshot['questions'] as $id => $question) {
                if ($question['type'] !== 'rating' || ! isset($answers[$id])) {
                    continue;
                }
                $scores[] = (int) $answers[$id];
                if ($question['threshold'] && $answers[$id] < $question['threshold']) {
                    $commentRules['comments.'.$id] = ['nullable', 'string', 'max:3000'];
                    $attributes['comments.'.$id] = $question['comment'];
                }
            }
            $comments = $request->validate($commentRules, [], $attributes)['comments'] ?? [];
            $invitation->update([
                'answers' => ['values' => $answers, 'comments' => $comments],
                'rating' => $scores === [] ? null : array_sum($scores) / count($scores),
                'submitted_at' => now(),
            ]);

            $delivery->awardCoupon($invitation);

            if ($scores !== [] && min($scores) === 5) {
                $delivery->queueReview($invitation);
            }
        }, 3);

        return new RedirectResponse($request->getRequestUri());
    }
}
