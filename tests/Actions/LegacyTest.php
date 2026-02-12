<?php

namespace MagicLink\Test\Actions;

use Illuminate\Support\Facades\DB;
use MagicLink\Actions\ControllerAction;
use MagicLink\Actions\DownloadFileAction;
use MagicLink\Actions\LoginAction;
use MagicLink\Actions\ResponseAction;
use MagicLink\Actions\ViewAction;
use MagicLink\MagicLink;
use MagicLink\Test\TestCase;
use MagicLink\Test\TestSupport\MyController;
use MagicLink\Test\TestSupport\User;

class LegacyTest extends TestCase
{
    public function test_controller()
    {
        $action = new ControllerAction(MyController::class);

        $magiclinkUrl = $this->generateMagicLink($action);

        $this->get($magiclinkUrl)
            ->assertStatus(419);
    }

    public function test_download_file()
    {
        $magiclinkUrl = $this->generateMagicLink(new DownloadFileAction('text.txt'));

        $this->get($magiclinkUrl)
            ->assertStatus(419);
    }

    public function test_auth()
    {
        $magiclinkUrl = $this->generateMagicLink(new LoginAction(User::first()));

        $this->get($magiclinkUrl)
            ->assertStatus(419);
    }

    public function test_response_callable()
    {
        $magiclinkUrl = $this->generateMagicLink(new ResponseAction(
            function () {
                return 'callback called';
            }
        ));

        $this->get($magiclinkUrl)
            ->assertStatus(419)
            ->assertDontSee('callback called');
    }

    public function test_view()
    {
        $magiclinkUrl = $this->generateMagicLink(new ViewAction('view'));

        $this->get($magiclinkUrl)
            ->assertStatus(419)
            ->assertDontSee('This is a tests view');
    }

    private function generateMagicLink($action): string
    {
        $id = (string) \Illuminate\Support\Str::uuid();
        $token = 'toktok';
        $payload = (new MagicLink)->getConnection()->getDriverName() === 'pgsql'
            ? base64_encode(serialize($action))
            : serialize($action);

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

        return "/magiclink/{$id}:{$token}";
    }
}
