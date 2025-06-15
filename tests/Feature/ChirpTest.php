<?php

namespace Tests\Feature;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_chirps_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/chirps');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertSee('chirps.create');
        $response->assertSee('chirps.list');
    }

    public function test_unauthenticated_user_cannot_view_chirps_page(): void
    {
        $response = $this->get('/chirps');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_chirp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('chirps.create')
            ->set('message', 'This is a test chirp')
            ->call('store')
            ->assertDispatched('chirp-created');

        $this->assertDatabaseHas('chirps', [
            'message' => 'This is a test chirp',
            'user_id' => $user->id,
        ]);
    }

    public function test_chirp_message_is_required(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('chirps.create')
            ->set('message', '')
            ->call('store')
            ->assertHasErrors(['message' => 'required']);
    }

    public function test_chirp_message_cannot_exceed_255_characters(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('chirps.create')
            ->set('message', str_repeat('a', 256))
            ->call('store')
            ->assertHasErrors(['message' => 'max']);
    }

    public function test_user_can_edit_own_chirp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('chirps.edit', ['chirp' => $chirp])
            ->set('message', 'Updated chirp message')
            ->call('update')
            ->assertDispatched('chirp-updated');

        $this->assertDatabaseHas('chirps', [
            'id' => $chirp->id,
            'message' => 'Updated chirp message',
        ]);
    }

    public function test_user_cannot_edit_others_chirp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create(['user_id' => $otherUser->id]);

        Livewire::actingAs($user)
            ->test('chirps.edit', ['chirp' => $chirp])
            ->set('message', 'Trying to edit others chirp')
            ->call('update')
            ->assertForbidden();
    }

    public function test_user_can_delete_own_chirp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('chirps.list')
            ->call('delete', $chirp);

        $this->assertDatabaseMissing('chirps', [
            'id' => $chirp->id,
        ]);
    }

    public function test_user_cannot_delete_others_chirp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create(['user_id' => $otherUser->id]);

        Livewire::actingAs($user)
            ->test('chirps.list')
            ->call('delete', $chirp)
            ->assertForbidden();
    }

    public function test_chirps_are_displayed_on_chirps_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Chirp $chirp */
        $chirp = Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Test chirp message',
        ]);

        Livewire::actingAs($user)
            ->test('chirps.list')
            ->assertSee('Test chirp message');
    }

    public function test_chirps_are_ordered_by_latest_first(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Chirp $oldChirp */
        $oldChirp = Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'Old chirp',
            'created_at' => now()->subHour(),
        ]);

        /** @var Chirp $newChirp */
        $newChirp = Chirp::factory()->create([
            'user_id' => $user->id,
            'message' => 'New chirp',
            'created_at' => now(),
        ]);

        $chirps = Livewire::actingAs($user)
            ->test('chirps.list')
            ->get('chirps');

        /** @var Chirp $firstChirp */
        $firstChirp = $chirps->first();
        /** @var Chirp $lastChirp */
        $lastChirp = $chirps->last();

        $this->assertEquals($newChirp->id, $firstChirp->id);
        $this->assertEquals($oldChirp->id, $lastChirp->id);
    }

    public function test_create_component_clears_message_after_successful_submission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('chirps.create')
            ->set('message', 'Test message')
            ->call('store')
            ->assertSet('message', '');
    }
}
