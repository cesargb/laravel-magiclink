<?php

namespace MagicLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use MagicLink\Actions\ActionAbstract;
use MagicLink\MagicLink;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrateLegacyActionsCommand extends Command
{
    protected $signature = 'magiclink:migrate
                                {--force : Force the operation to run without confirmation}';

    protected $description = 'Migrate legacy serialized actions to the new HMAC-signed format';

    private int $migrated = 0;
    private array $errors = [];
    private ProgressBar $progressBar;

    public function handle()
    {
        $this->info('Checking for legacy MagicLinks...');

        $legacyCount = $this->getLegacyBuilder()->count();

        if ($legacyCount === 0) {
            $this->info('No legacy MagicLinks found, nothing to migrate.');

            return 0;
        }

        if (! $this->canContinue($legacyCount)) {
            $this->info('Migration cancelled.');

            return 1;
        }

        $this->info('Starting migration...');
        $this->progressBar = $this->output->createProgressBar($legacyCount);
        $this->progressBar->start();

        $this->getLegacyBuilder()->chunkById(100, function ($magicLinks) {
            foreach ($magicLinks as $magicLink) {
                $result = $this->migrateRecord($magicLink->id, $magicLink->action);

                $this->progressBar->advance();

                if ($result['status'] !== 'success') {
                    $this->errors[] = $result;
                    continue;
                }

                $this->migrated++;
            }
        });

        $this->progressBar->finish();

        $this->printSummary();

        return 0;
    }

    private function canContinue(int $legacyCount): bool
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm("Found {$legacyCount} legacy MagicLinks to migrate, do you want to proceed?");
    }

    private function printSummary(): void
    {
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->info("Successfully migrated: {$this->migrated}");

        if ($failed = count($this->errors) > 0) {
            $this->error("Failed to migrate: {$failed}");
            $this->table(['Id', 'Error'], array_map(fn ($e) => [$e['id'], $e['error']], $this->errors));
        }
    }

    private function getLegacyBuilder(): Builder
    {
        $tableName = config('magiclink.magiclink_table', 'magic_links');

        return DB::table($tableName)->where('action', 'like', 'O:%');
    }

    private function migrateRecord(int|string $id, string $action): array
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
