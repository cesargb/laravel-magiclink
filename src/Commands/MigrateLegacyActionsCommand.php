<?php

namespace MagicLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use MagicLink\Actions\ActionAbstract;
use MagicLink\MagicLink;

class MigrateLegacyActionsCommand extends Command
{
    protected $signature = 'magiclink:migrate
                                {--force : Force the operation to run without confirmation}';

    protected $description = 'Migrate legacy serialized actions to the new HMAC-signed format';

    public function handle()
    {
        $this->info('Checking for legacy MagicLinks...');

        $legacyCount = $this->getLegacyBuilder()->count();

        if ($legacyCount === 0) {
            $this->info('No legacy MagicLinks found, nothing to migrate.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm("Found {$legacyCount} legacy MagicLinks to migrate, do you want to proceed?")) {
            $this->info('Migration cancelled.');

            return 1;
        }

        $this->info('Starting migration...');
        $progressBar = $this->output->createProgressBar($legacyCount);
        $progressBar->start();

        $migrated = 0;
        $errors = [];

        $this->getLegacyBuilder()->orderBy('id', 'desc')->chunk(100, function ($magicLinks) use (&$migrated, &$errors, $progressBar) {
            foreach ($magicLinks as $magicLink) {
                $result = $this->migrateRecord($magicLink->id, $magicLink->action);

                if ($result['status'] === 'success') {
                    $migrated++;
                } else {
                    $errors[] = $result;
                }

                $progressBar->advance();
            }
        });

        $failed = count($errors);

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->info("Successfully migrated: {$migrated}");

        if ($failed > 0) {
            $this->error("Failed to migrate: {$failed}");
            $this->table(['Id', 'Error'], array_map(fn ($e) => [$e['id'], $e['error']], $errors));
        }

        return $failed > 0 ? 1 : 0;
    }

    private function getLegacyBuilder(): Builder
    {
        $tableName = config('magiclink.magiclink_table', 'magic_links');

        return DB::table($tableName)->where('action', 'like', 'O:%');
    }

    private function migrateRecord(string $id, string $action): array
    {
        $magicLink = MagicLink::find($id);

        if (! $magicLink) {
            return [
                'status' => 'error',
                'id' => $id,
                'error' => 'MagicLink not found.',
            ];
        }

        if (! $this->isAllowedAction($action)) {
            return [
                'status' => 'error',
                'id' => $id,
                'error' => 'Action class is not allowed for migration.',
            ];
        }

        $magicLink->action = unserialize($action);
        $magicLink->saveQuietly();

        return [
            'status' => 'success',
            'id' => $id,
        ];
    }

    private function isAllowedAction(string $data): bool
    {
        if (preg_match('/^O:\d+:"([^"]+)"/', $data, $matches)) {
            $className = $matches[1];

            return is_subclass_of($className, ActionAbstract::class);
        }

        return false;
    }
}
