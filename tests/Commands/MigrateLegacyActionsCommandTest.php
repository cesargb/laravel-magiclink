<?php

namespace MagicLink\Test\Commands;

use Illuminate\Support\Facades\DB;
use MagicLink\Actions\ControllerAction;
use MagicLink\Actions\DownloadFileAction;
use MagicLink\Actions\LoginAction;
use MagicLink\Actions\ResponseAction;
use MagicLink\Actions\ViewAction;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\MyController;
use MagicLink\Test\TestSupport\User;

class MigrateLegacyActionsCommandTest extends TestCase
{
    public function test_controller()
    {
        $action = new ControllerAction(MyController::class);

        [$id, $magiclinkUrl] = $this->generateMagicLink($action);

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('im a controller invoke');

        $this->artisan('magiclink:migrate', ['--force' => true])
            ->assertSuccessful();

        $actionMigrated = json_decode(DB::table('magic_links')->where('id', $id)->first()->action);

        $this->assertEquals(ControllerAction::class, $actionMigrated->type);

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('im a controller invoke');
    }

    public function test_download_file()
    {
        [$id, $magiclinkUrl] = $this->generateMagicLink(new DownloadFileAction('text.txt'));

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename=text.txt'
            );

        $this->artisan('magiclink:migrate', ['--force' => true])
            ->assertSuccessful();

        $actionMigrated = json_decode(DB::table('magic_links')->where('id', $id)->first()->action);

        $this->assertEquals(DownloadFileAction::class, $actionMigrated->type);

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertHeader(
                'content-disposition',
                'attachment; filename=text.txt'
            );
    }

    public function test_auth()
    {
        [$id, $magiclinkUrl] = $this->generateMagicLink(new LoginAction(User::first()));

        $this->artisan('magiclink:migrate', ['--force' => true])
            ->assertSuccessful();

        $actionMigrated = json_decode(DB::table('magic_links')->where('id', $id)->first()->action);

        $this->assertEquals(LoginAction::class, $actionMigrated->type);

        $this->get($magiclinkUrl)
            ->assertStatus(302)
            ->assertRedirect('/');

        $this->assertAuthenticatedAs(User::first());
    }

    public function test_response_callable()
    {
        [$id, $magiclinkUrl] = $this->generateMagicLink(new ResponseAction(
            function () {
                return 'callback called';
            }
        ));

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('callback called');

        $this->artisan('magiclink:migrate', ['--force' => true])
            ->assertSuccessful();

        $actionMigrated = json_decode(DB::table('magic_links')->where('id', $id)->first()->action);

        $this->assertEquals(ResponseAction::class, $actionMigrated->type);

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('callback called');
    }

    public function test_view()
    {
        [$id, $magiclinkUrl] = $this->generateMagicLink(new ViewAction('view'));

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('This is a tests view');

        $this->artisan('magiclink:migrate', ['--force' => true])
            ->assertSuccessful();

        $actionMigrated = json_decode(DB::table('magic_links')->where('id', $id)->first()->action);

        $this->assertEquals(ViewAction::class, $actionMigrated->type);

        $this->get($magiclinkUrl)
            ->assertStatus(200)
            ->assertSeeText('This is a tests view');
    }

    private function generateMagicLink($action): array
    {
        $id = (string) \Illuminate\Support\Str::uuid();
        $token = 'toktok';
        $payload = serialize($action);

        DB::table('magic_links')->insert([
            'id' => $id,
            'token' => $token,
            'action' => $payload,
            'num_visits' => 0,
            'max_visits' => null,
            'available_at' => now()->addDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            $id,
            "/magiclink/{$id}:{$token}",
        ];
    }
}
