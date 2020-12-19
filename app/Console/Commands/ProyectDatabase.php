<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

class ProyectDatabase extends Command
{
    use CallReturn;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proyect:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database configuration';

    /** database check */
    protected $db_error = false;
    protected $migrated = false;
    protected $passport = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        try {
            $db = env('DB_DATABASE');
            $tables = DB::select("show tables where Tables_in_{$db} = 'migrations'");
            $this->migrated = !empty($tables);
        } catch (\Exception $e) {
            $this->error('Database error, check the connection or start the proyect running proyect:start');
            $this->db_error = true;
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->db_error) return;

        if (!$this->migrated) {
            $this->info('Starting migration (first time).');
            $this->call('migrate');
        } else {
            $this->comment('You run migrations previously.');

            $choices = $this->migrationChoices();
            $values = array_map(function($value) { return $value[1]; }, $choices);
            $value = $this->choice('What do you want to do?', $values, 0);
            $id = array_search($value, $values);
            if ($id === 0) {
                $this->comment('Exit from database setup.');
                return;
            }

            $command = $choices[$id][0];
            if ($command) $this->call($command);
        }

        if ($command === 'migrate:status') {
            $hasOauth = $this->hasOauthTables() && $this->hasOauth();
            $this->info('Passport installed: ' . ($hasOauth ? 'Yes' : 'No'));
            return;
        }

        if ($this->hasOauthTables() && !$this->hasOauth()) {
            $this->info('Passport install.');
            $this->call('passport:install');
        }
    }

    protected function migrationChoices()
    {
        $choices = [ ['', 'Exit.'] ];
        $output = $this->callReturn('migrate:status');
        $pending = !!preg_match('/\|\s+No\s+\|/', $output);
        if ($pending) {
            $choices[] = ['migrate', 'Add pending migrations', false];
        }

        $choices[] = ['migrate:fresh', 'Drop all tables and run migrations', true];
        $choices[] = ['migrate:rollback', 'Rollback the last migration'];
        $choices[] = ['migrate:status', 'Check migration status'];
        if ($this->hasOauthTables() && !$this->hasOauth()) {
            $choices[] = ['passport:install', 'Prepare passport'];
        }

        return array_map(function($value) {
            if ($value[0] !== 'exit') $value[1] = "{$value[1]} ({$value[0]}).";
            return $value;
        }, $choices);
    }

    protected function hasOauthTables()
    {
        $db = env('DB_DATABASE');
        $tables = DB::select("show tables where Tables_in_{$db} like 'oauth_%'");
        if (empty($tables)) return false;

        return true;
    }

    protected function hasOauth()
    {
        $clients = DB::select('select id from oauth_clients');
        if (empty($clients)) return false;

        $access = DB::select('select id from oauth_personal_access_clients');
        if (empty($access)) return false;

        return true;
    }
}
