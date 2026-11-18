<?php

namespace App\Livewire\Polls;

use App\Models\Coupon;
use App\Models\Poll;
use App\Services\CustomerLanguageService;
use App\Services\Marketing\MarketingAccess;
use App\Services\Polls\PollDelivery;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PollEditor extends Component
{
    #[Locked]
    public ?string $pollId = null;

    public string $label = '';

    public ?string $rewardCouponId = null;

    public bool $enabled = false;

    public $sendAfterHours = 2;

    public $reviewMinMinutes = 15;

    public $reviewMaxMinutes = 45;

    public bool $reviewEnabled = false;

    public array $translations = [];

    public array $questions = [];

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function mount($poll = null): void
    {
        if (is_string($poll)) {
            $poll = Poll::findOrFail($poll);
        }
        if ($poll?->exists) {
            $this->pollId = $poll->id;
            $this->label = $poll->label;
            $this->rewardCouponId = $poll->reward_coupon_id;
            $this->enabled = $poll->enabled_at !== null;
            $this->sendAfterHours = $poll->send_after_hours;
            $this->reviewMinMinutes = $poll->review_min_minutes;
            $this->reviewMaxMinutes = $poll->review_max_minutes;
            $this->translations = $poll->translations;
            $this->questions = $poll->questions;
            foreach ($this->questions as &$question) {
                foreach ($question['texts'] as &$text) {
                    $text['options'] = implode("\n", $text['choices'] ?? []);
                }
                unset($text);
            }
            unset($question);
            $this->reviewEnabled = $poll->review_enabled;

            return;
        }

        $this->addQuestion();
    }

    public function addQuestion(): void
    {
        if (count($this->questions) < 30) {
            $this->questions[] = [
                'id' => (string) Str::uuid(), 'type' => 'rating', 'required' => true, 'threshold' => null,
                'texts' => [],
            ];
        }
    }

    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
        $this->resetValidation();
    }

    public function moveQuestion(int $index, int $direction): void
    {
        $next = $index + $direction;
        if (abs($direction) === 1 && isset($this->questions[$index], $this->questions[$next])) {
            [$this->questions[$index], $this->questions[$next]] = [$this->questions[$next], $this->questions[$index]];
            $this->resetValidation();
        }
    }

    public function save()
    {
        $languages = array_keys($this->languages());
        $rules = [
            'rewardCouponId' => ['nullable', 'uuid', 'exists:coupons,id'],
            'label' => ['required', 'string', 'max:255'],
            'sendAfterHours' => ['required', 'integer', 'between:1,720'],
            'questions' => ['required', 'list', 'max:30'],
            'questions.*.id' => ['required', 'uuid', 'distinct'],
            'questions.*.type' => ['required', Rule::in(Poll::TYPES)],
            'questions.*.required' => ['required', 'boolean'],
            'questions.*.threshold' => ['nullable', 'integer', 'between:2,5'],
        ];
        $attributes = [
            'label' => __('polls.label'),
            'rewardCouponId' => __('polls.reward_coupon'),
            'sendAfterHours' => __('polls.send_after'),
            'reviewMinMinutes' => __('polls.review_min'),
            'reviewMaxMinutes' => __('polls.review_max'),
            'questions' => __('polls.questions'),
        ];
        $fields = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:6000'],
        ];
        if ($this->reviewEnabled) {
            $rules['reviewMinMinutes'] = ['required', 'integer', 'between:1,10080'];
            $rules['reviewMaxMinutes'] = ['required', 'integer', 'gte:reviewMinMinutes', 'max:10080'];
        }
        $reviewRequired = $this->reviewEnabled ? 'required' : 'nullable';
        $fields['review_subject'] = [$reviewRequired, 'string', 'max:255'];
        $fields['review_message'] = [$reviewRequired, 'string', 'max:6000'];
        foreach ($languages as $language) {
            foreach ($fields as $field => $fieldRules) {
                $key = "translations.$language.$field";
                $rules[$key] = $fieldRules;
                $attributes[$key] = __($field === 'title' ? 'polls.title_field' : "polls.$field").' ('.strtoupper($language).')';
            }
        }
        foreach ($this->questions as $index => $question) {
            $type = data_get($question, 'type');
            $textRules = ['label' => ['required', 'string', 'max:255']];
            if ($type === 'rating' && filled(data_get($question, 'threshold'))) {
                $textRules['comment'] = ['required', 'string', 'max:255'];
            }
            if (in_array($type, ['choice', 'checkbox'], true)) {
                $textRules['options'] = ['required', 'string', 'max:3000'];
            }
            foreach ($languages as $language) {
                foreach ($textRules as $field => $fieldRules) {
                    $key = "questions.$index.texts.$language.$field";
                    $rules[$key] = $fieldRules;
                    $attributes[$key] = __('polls.question_'.$field).' ('.($index + 1).' · '.strtoupper($language).')';
                }
            }
        }
        $data = $this->validate($rules, [], $attributes);

        $questions = [];
        $optionErrors = [];
        foreach ($data['questions'] as $index => $question) {
            $threshold = $question['type'] === 'rating' ? ($question['threshold'] ?? null) : null;
            $texts = [];
            $optionCount = null;
            foreach ($languages as $language) {
                $text = $question['texts'][$language];
                $texts[$language] = ['label' => trim($text['label']), 'comment' => $threshold ? trim($text['comment']) : ''];
                if (isset($text['options'])) {
                    $choices = $this->choices($text['options']);
                    $optionCount ??= count($choices);
                    if ($optionCount !== count($choices) || count($choices) < 2 || count($choices) > 15) {
                        $optionErrors["questions.$index.texts.$language.options"] = __('polls.options_error');
                    }
                    $texts[$language]['choices'] = $choices;
                }
            }
            $questions[] = Arr::only($question, ['id', 'type', 'required']) + ['threshold' => $threshold ?: null, 'texts' => $texts];
        }
        if ($optionErrors !== []) {
            throw ValidationException::withMessages($optionErrors);
        }

        $poll = DB::transaction(function () use ($languages, $data, $questions): Poll {
            $poll = $this->pollId ? Poll::lockForUpdate()->findOrFail($this->pollId) : new Poll;
            $poll->fill([
                'label' => trim($data['label']),
                'reward_coupon_id' => ($data['rewardCouponId'] ?? null) ?: null,
                'enabled_at' => $this->enabled ? ($poll->enabled_at ?? now()) : null,
                'send_after_hours' => $data['sendAfterHours'],
                'review_enabled' => $this->reviewEnabled,
                'review_min_minutes' => $data['reviewMinMinutes'] ?? $poll->review_min_minutes ?? 15,
                'review_max_minutes' => $data['reviewMaxMinutes'] ?? $poll->review_max_minutes ?? 45,
                'default_language' => $languages[0],
                'translations' => $data['translations'],
                'questions' => $questions,
            ])->save();

            return $poll;
        });
        session()->flash('success', __('polls.saved'));

        return $this->redirectRoute('polls.edit', $poll);
    }

    private function choices(string $options): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $options)), fn ($choice) => $choice !== ''));
    }

    private function languages(): array
    {
        return app(CustomerLanguageService::class)->enabled();
    }

    public function render()
    {
        return view('livewire.polls.editor', [
            'languages' => $this->languages(),
            'types' => Poll::TYPES,
            'coupons' => Coupon::orderBy('name')->get(),
            'placeholders' => PollDelivery::PLACEHOLDERS,
        ])->title(__($this->pollId ? 'polls.edit' : 'polls.create'));
    }
}
