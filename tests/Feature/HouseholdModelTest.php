<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_hogar_conserva_las_membresias_y_su_estado(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $household = new Household(['name' => 'Hogar de prueba']);
        $household->creator()->associate($owner);
        $household->save();
        $household->users()->attach($owner, ['role' => 'admin']);
        $household->users()->attach($member, ['role' => 'viewer', 'deactivated_at' => now()]);

        $this->assertSame('MXN', $household->fresh()->currency);
        $this->assertSame($owner->id, $household->creator->id);
        $this->assertSame('viewer', $member->households()->first()->pivot->role);
        $this->assertNotNull($member->households()->first()->pivot->deactivated_at);
    }

    public function test_no_permite_membresias_duplicadas(): void
    {
        $user = User::factory()->create();
        $household = new Household(['name' => 'Hogar de prueba']);
        $household->creator()->associate($user);
        $household->save();
        $household->users()->attach($user, ['role' => 'administrator']);

        $this->expectException(QueryException::class);
        $household->users()->attach($user, ['role' => 'admin']);
    }
}
