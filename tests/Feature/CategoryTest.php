<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_category_pages(): void
    {
        $this->get(route('categories.index'))->assertRedirect('/login');
        $this->get(route('categories.create'))->assertRedirect('/login');
    }

    public function test_index_groups_categories_by_type_and_hides_other_users_data(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Category::factory()->for($user)->expense()->create(['name' => 'Makanan Saya']);
        Category::factory()->for($user)->income()->create(['name' => 'Gaji Saya']);
        Category::factory()->for($other)->expense()->create(['name' => 'Kategori Orang Lain']);

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Makanan Saya');
        $response->assertSee('Gaji Saya');
        $response->assertDontSee('Kategori Orang Lain');
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Olahraga',
            'type' => CategoryType::Expense->value,
            'color' => '#ff0000',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Olahraga',
            'type' => CategoryType::Expense->value,
            'color' => '#ff0000',
        ]);
    }

    public function test_category_name_must_be_unique_per_type_and_user(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->expense()->create(['name' => 'Lainnya']);

        $duplicate = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Lainnya',
            'type' => CategoryType::Expense->value,
        ]);
        $duplicate->assertSessionHasErrors(['name']);

        $otherType = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Lainnya',
            'type' => CategoryType::Income->value,
        ]);
        $otherType->assertSessionHasNoErrors();
    }

    public function test_user_cannot_edit_or_delete_another_users_category(): void
    {
        $user = User::factory()->create();
        $otherCategory = Category::factory()->for(User::factory())->expense()->create();

        $this->actingAs($user)->get(route('categories.edit', $otherCategory))->assertForbidden();
        $this->actingAs($user)->put(route('categories.update', $otherCategory), [
            'name' => 'Dicuri',
            'type' => CategoryType::Expense->value,
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('categories.destroy', $otherCategory))->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $otherCategory->id, 'name' => $otherCategory->name]);
    }

    public function test_category_used_by_transaction_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->expense()->create();
        $wallet = Wallet::factory()->for($user)->create();
        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->expense()->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
