<?php

namespace MagicLink\Test;

use Illuminate\Support\Facades\Artisan;
use MagicLink\Actions\LoginAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestSupport\User;

class MagicLinkPrunableTest extends TestCase
{
    public function test_prunable_scope_includes_expired_by_date()
    {
        $expiredLink = MagicLink::create(new LoginAction(User::first()), 60);
        $expiredLink->available_at = now()->subMinute();
        $expiredLink->save();

        $validLink = MagicLink::create(new LoginAction(User::first()), 60);

        $prunableQuery = (new MagicLink)->prunable();
        $prunableLinks = $prunableQuery->get();

        $this->assertTrue($prunableLinks->pluck('id')->contains($expiredLink->id));
        $this->assertFalse($prunableLinks->pluck('id')->contains($validLink->id));
    }

    public function test_prunable_scope_includes_expired_by_max_visits()
    {
        $expiredLink = MagicLink::create(new LoginAction(User::first()), null, 2);
        $expiredLink->num_visits = 2;
        $expiredLink->save();

        $validLink = MagicLink::create(new LoginAction(User::first()), null, 2);
        $validLink->num_visits = 1;
        $validLink->save();

        $prunableQuery = (new MagicLink)->prunable();
        $prunableLinks = $prunableQuery->get();

        $this->assertTrue($prunableLinks->pluck('id')->contains($expiredLink->id));
        $this->assertFalse($prunableLinks->pluck('id')->contains($validLink->id));
    }

    public function test_prunable_scope_excludes_links_with_null_available_at_and_no_max_visits()
    {
        $validLink = MagicLink::create(new LoginAction(User::first()), null);

        $prunableQuery = (new MagicLink)->prunable();
        $prunableLinks = $prunableQuery->get();

        $this->assertFalse($prunableLinks->contains($validLink));
    }

    public function test_prunable_scope_excludes_valid_links_with_future_date()
    {
        $validLink = MagicLink::create(new LoginAction(User::first()), 60);

        $prunableQuery = (new MagicLink)->prunable();
        $prunableLinks = $prunableQuery->get();

        $this->assertFalse($prunableLinks->contains($validLink));
    }

    public function test_prunable_scope_includes_links_exceeded_max_visits()
    {
        $exceededLink = MagicLink::create(new LoginAction(User::first()), null, 3);
        $exceededLink->num_visits = 5;
        $exceededLink->save();

        $prunableQuery = (new MagicLink)->prunable();
        $prunableLinks = $prunableQuery->get();

        $this->assertTrue($prunableLinks->pluck('id')->contains($exceededLink->id));
    }

    public function test_model_prune_command_deletes_expired_links()
    {
        $validLink = MagicLink::create(new LoginAction(User::first()), 60);

        $expiredByDate = MagicLink::create(new LoginAction(User::first()), 60);
        $expiredByDate->available_at = now()->subDay();
        $expiredByDate->save();

        $expiredByVisits = MagicLink::create(new LoginAction(User::first()), null, 1);
        $expiredByVisits->num_visits = 1;
        $expiredByVisits->save();

        $expiredByDateId = $expiredByDate->id;
        $expiredByVisitsId = $expiredByVisits->id;
        $validLinkId = $validLink->id;

        $countBeforePrune = MagicLink::count();
        $this->assertGreaterThanOrEqual(3, $countBeforePrune);

        Artisan::call('model:prune', ['--model' => MagicLink::class]);

        $this->assertNull(MagicLink::find($expiredByDateId));
        $this->assertNull(MagicLink::find($expiredByVisitsId));
        $this->assertNotNull(MagicLink::find($validLinkId));

        $countAfterPrune = MagicLink::count();
        $this->assertLessThan($countBeforePrune, $countAfterPrune);
    }

    public function test_expired_scope_identifies_links_correctly()
    {
        $validLink = MagicLink::create(new LoginAction(User::first()), 60);

        $expiredLink = MagicLink::create(new LoginAction(User::first()), 60);
        $expiredLink->available_at = now()->subMinute();
        $expiredLink->save();

        $expiredLinks = MagicLink::expired()->get();
        $expiredIds = $expiredLinks->pluck('id')->toArray();

        $this->assertContains((string) $expiredLink->id, $expiredIds);
        $this->assertNotContains((string) $validLink->id, $expiredIds);
    }

    public function test_expired_scope_with_mixed_conditions()
    {
        $validWithFutureDate = MagicLink::create(new LoginAction(User::first()), 60);
        $validWithNoLimits = MagicLink::create(new LoginAction(User::first()), null);

        $expiredByDate = MagicLink::create(new LoginAction(User::first()), 60);
        $expiredByDate->available_at = now()->subDay();
        $expiredByDate->save();

        $expiredByVisits = MagicLink::create(new LoginAction(User::first()), null, 2);
        $expiredByVisits->num_visits = 2;
        $expiredByVisits->save();

        $expiredLinks = MagicLink::expired()->get();
        $expiredIds = $expiredLinks->pluck('id')->toArray();

        $this->assertGreaterThanOrEqual(2, $expiredLinks->count());
        $this->assertContains((string) $expiredByDate->id, $expiredIds);
        $this->assertContains((string) $expiredByVisits->id, $expiredIds);
        $this->assertNotContains((string) $validWithFutureDate->id, $expiredIds);
        $this->assertNotContains((string) $validWithNoLimits->id, $expiredIds);
    }
}
