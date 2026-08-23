<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\RatingAnswer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_rating_question_without_options(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.questions.store'), [
            'question' => 'Bagaimana keramahan driver?',
            'indicator' => 'Sikap & Etika',
            'target_type' => Question::TARGET_DRIVER,
            'answer_type' => Question::TYPE_RATING,
            'is_required' => '1',
            'weight' => 100,
            'sort_order' => 2,
            'status' => Question::STATUS_ACTIVE,
            'options' => [
                ['option_text' => 'Tidak boleh tersimpan', 'sort_order' => 1],
            ],
        ])->assertRedirect();

        $question = Question::query()->where('question', 'Bagaimana keramahan driver?')->firstOrFail();

        $this->assertSame(Question::TYPE_RATING, $question->answer_type);
        $this->assertCount(0, $question->options);
    }

    public function test_admin_can_manage_multiple_choice_options(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.questions.store'), [
            'question' => 'Bagian kendaraan mana yang perlu diperbaiki?',
            'indicator' => 'Tidak dipakai',
            'target_type' => Question::TARGET_VEHICLE,
            'answer_type' => Question::TYPE_MULTIPLE_CHOICE,
            'is_required' => '0',
            'weight' => 100,
            'sort_order' => 5,
            'status' => Question::STATUS_ACTIVE,
            'options' => [
                ['option_text' => 'AC', 'sort_order' => 2],
                ['option_text' => 'Kursi', 'sort_order' => 1],
            ],
        ])->assertRedirect();

        $question = Question::query()->where('answer_type', Question::TYPE_MULTIPLE_CHOICE)->firstOrFail();
        $this->assertSame(['Kursi', 'AC'], $question->options->pluck('option_text')->all());

        $this->put(route('admin.questions.update', $question), [
            'question' => 'Bagian kendaraan yang perlu diperbaiki?',
            'indicator' => 'Tidak dipakai',
            'target_type' => Question::TARGET_VEHICLE,
            'answer_type' => Question::TYPE_CHECKBOX,
            'is_required' => '1',
            'weight' => 100,
            'sort_order' => 4,
            'status' => Question::STATUS_ACTIVE,
            'options' => [
                ['option_text' => 'Kebersihan', 'sort_order' => 1],
                ['option_text' => 'Lampu', 'sort_order' => 2],
            ],
        ])->assertRedirect(route('admin.questions.index'));

        $this->assertSame(Question::TYPE_CHECKBOX, $question->fresh()->answer_type);
        $this->assertSame(['Kebersihan', 'Lampu'], $question->fresh()->options->pluck('option_text')->all());
    }

    public function test_option_required_for_choice_answer_types(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.questions.store'), [
            'question' => 'Pilih kondisi kendaraan',
            'indicator' => 'Tidak dipakai',
            'target_type' => Question::TARGET_VEHICLE,
            'answer_type' => Question::TYPE_CHECKBOX,
            'is_required' => '1',
            'weight' => 100,
            'sort_order' => 1,
            'status' => Question::STATUS_ACTIVE,
            'options' => [
                ['option_text' => '', 'sort_order' => 1],
            ],
        ])->assertSessionHasErrors('options');
    }

    public function test_admin_can_toggle_active_inactive_question(): void
    {
        $this->actingAs(User::factory()->create());
        $question = Question::factory()->create(['status' => Question::STATUS_ACTIVE]);

        $this->patch(route('admin.questions.toggle-status', $question))->assertRedirect();

        $this->assertSame(Question::STATUS_INACTIVE, $question->fresh()->status);
    }

    public function test_question_weight_cannot_exceed_one_hundred_percent_per_target(): void
    {
        $this->actingAs(User::factory()->create());
        Question::factory()->create([
            'target_type' => Question::TARGET_DRIVER,
            'indicator' => 'Sikap & Etika',
            'weight' => 85,
            'status' => Question::STATUS_INACTIVE,
        ]);

        $this->post(route('admin.questions.store'), [
            'question' => 'Apakah driver tepat waktu?',
            'indicator' => 'Ketepatan Waktu',
            'target_type' => Question::TARGET_DRIVER,
            'answer_type' => Question::TYPE_RATING,
            'is_required' => '1',
            'weight' => 20,
            'sort_order' => 2,
            'status' => Question::STATUS_INACTIVE,
        ])->assertSessionHasErrors('weight');
    }

    public function test_question_can_only_be_activated_when_target_weight_is_one_hundred_percent(): void
    {
        $this->actingAs(User::factory()->create());
        $question = Question::factory()->create([
            'target_type' => Question::TARGET_DRIVER,
            'weight' => 75,
            'status' => Question::STATUS_INACTIVE,
        ]);

        $this->patch(route('admin.questions.toggle-status', $question))
            ->assertSessionHas('error');

        $this->assertSame(Question::STATUS_INACTIVE, $question->fresh()->status);
    }

    public function test_vehicle_indicator_is_set_automatically(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.questions.store'), [
            'question' => 'Apakah kendaraan bersih?',
            'indicator' => 'Tidak dipakai',
            'target_type' => Question::TARGET_VEHICLE,
            'answer_type' => Question::TYPE_RATING,
            'is_required' => '1',
            'weight' => 100,
            'sort_order' => 1,
            'status' => Question::STATUS_ACTIVE,
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'question' => 'Apakah kendaraan bersih?',
            'indicator' => Question::VEHICLE_INDICATOR,
        ]);
    }

    public function test_questions_are_sorted_by_sort_order(): void
    {
        $this->actingAs(User::factory()->create());
        Question::factory()->create(['question' => 'Ketiga', 'sort_order' => 3]);
        Question::factory()->create(['question' => 'Pertama', 'sort_order' => 1]);
        Question::factory()->create(['question' => 'Kedua', 'sort_order' => 2]);

        $this->get(route('admin.questions.index'))
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua', 'Ketiga']);
    }

    public function test_admin_can_reorder_questions_globally(): void
    {
        $this->actingAs(User::factory()->create());
        $first = Question::factory()->create(['question' => 'Pertama', 'sort_order' => 1]);
        $second = Question::factory()->create(['question' => 'Kedua', 'sort_order' => 2]);
        $third = Question::factory()->create(['question' => 'Ketiga', 'sort_order' => 3]);

        $this->get(route('admin.questions.index', ['reorder' => 1]))
            ->assertOk()
            ->assertSee('Mode ubah urutan aktif')
            ->assertSeeInOrder(['Pertama', 'Kedua', 'Ketiga']);

        $this->patch(route('admin.questions.reorder'), [
            'order' => [$third->id, $first->id, $second->id],
        ])->assertRedirect(route('admin.questions.index'));

        $this->assertSame(
            [$third->id, $first->id, $second->id],
            Question::query()->ordered()->pluck('id')->all(),
        );
    }

    public function test_question_preview_renders_answer_type_examples(): void
    {
        $this->actingAs(User::factory()->create());
        $rating = Question::factory()->create([
            'question' => 'Bagaimana keramahan driver?',
            'answer_type' => Question::TYPE_RATING,
        ]);
        $yesNo = Question::factory()->create([
            'question' => 'Apakah kendaraan bersih?',
            'answer_type' => Question::TYPE_YES_NO,
        ]);

        $this->get(route('admin.questions.show', $rating))
            ->assertOk()
            ->assertSee('Bagaimana keramahan driver?')
            ->assertSeeInOrder(['1', '2', '3', '4', '5']);

        $this->get(route('admin.questions.show', $yesNo))
            ->assertOk()
            ->assertSee('Apakah kendaraan bersih?')
            ->assertSee('Ya')
            ->assertSee('Tidak');
    }

    public function test_question_edit_navigation_returns_to_its_origin(): void
    {
        $this->actingAs(User::factory()->create());
        $question = Question::factory()->create();

        $this->get(route('admin.questions.edit', $question))
            ->assertOk()
            ->assertSee(route('admin.questions.index'), false)
            ->assertDontSee('<div class="page-header">', false);

        $this->get(route('admin.questions.edit', ['question' => $question, 'return_to' => 'detail']))
            ->assertOk()
            ->assertSee(route('admin.questions.show', $question), false);
    }

    public function test_question_with_rating_answers_is_deactivated_instead_of_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $question = Question::factory()->create(['status' => Question::STATUS_ACTIVE]);
        RatingAnswer::factory()->for($question)->create();

        $this->delete(route('admin.questions.destroy', $question))->assertRedirect();

        $this->assertSame(Question::STATUS_INACTIVE, $question->fresh()->status);
        $this->assertNotNull($question->fresh());
    }
}
