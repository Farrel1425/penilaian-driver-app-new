<?php

namespace Tests\Feature;

use App\Models\IndicatorCategory;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_indicator_category(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.indicator-categories.store'), [
            'name' => 'Keselamatan Mengemudi',
            'target_type' => Question::TARGET_DRIVER,
            'sort_order' => 1,
            'status' => IndicatorCategory::STATUS_ACTIVE,
        ])->assertRedirect(route('admin.indicator-categories.index'));

        $this->assertDatabaseHas('indicator_categories', [
            'name' => 'Keselamatan Mengemudi',
            'target_type' => Question::TARGET_DRIVER,
        ]);
    }

    public function test_category_name_is_unique_per_target(): void
    {
        $this->actingAs(User::factory()->create());
        IndicatorCategory::factory()->create([
            'name' => 'Kebersihan',
            'target_type' => Question::TARGET_DRIVER,
        ]);

        $this->post(route('admin.indicator-categories.store'), [
            'name' => 'Kebersihan',
            'target_type' => Question::TARGET_DRIVER,
            'sort_order' => 2,
            'status' => IndicatorCategory::STATUS_ACTIVE,
        ])->assertSessionHasErrors('name');

        $this->post(route('admin.indicator-categories.store'), [
            'name' => 'Kebersihan',
            'target_type' => Question::TARGET_VEHICLE,
            'sort_order' => 1,
            'status' => IndicatorCategory::STATUS_ACTIVE,
        ])->assertSessionDoesntHaveErrors();
    }

    public function test_used_category_is_deactivated_instead_of_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $category = IndicatorCategory::factory()->create();
        Question::factory()->for($category, 'indicatorCategory')->create();

        $this->delete(route('admin.indicator-categories.destroy', $category))->assertRedirect();

        $this->assertSame(IndicatorCategory::STATUS_INACTIVE, $category->fresh()->status);
    }
}
