<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProyectStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        proyect:start
        {name? : app name}
        {--o : overwrite .env file}
        {--k : generate key}
        {--database= : database name}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure a basic proyect';

    protected $env;
    protected $example;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $dir = getcwd();
        $this->env = "{$dir}/.env";
        $this->example = "{$dir}/.env.example";
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        #check .env file
        $new = !file_exists($this->env);
        if (!$new) {
            $this->comment('.env file already exists.');
            if ($this->option('o') || $this->confirm('Overwrite .env file?')) {
                if ($this->copyEnv()) {
                    $new = true;
                } else {
                    return;
                }
            }
        }

        #check app key
        if ($new || !$this->getKey('APP_KEY') || $this->confirm('Generate new key value?')) {
            $this->call('key:generate', ['--ansi']);
        }

        #check app info
        $this->editKey('APP_NAME', 'App Name', $this->argument('name'));
        $this->editKey('APP_URL', 'Url');

        #check database info
        $this->comment('Database info:');
        $this->editKey('DB_DATABASE', 'Name');
        $this->editKey('DB_USERNAME', 'User');
        $this->editKey('DB_PASSWORD', 'Password');
    }

    public function copyEnv()
    {
        if (!file_exists($this->example)) {
            $this->error('Canceled. Can\'t find .env.example file.');
            return false;
        }

        copy($this->example, $this->env);
        if (!file_exists($this->env)) {
            $this->error('Can\'t created .env file.');
            return false;
        }

        $this->info('Created .env file...');
        return true;
    }

    public function editKey($key, $description = null, $value = null)
    {
        if ($value) return $this->setKey($key, $value);

        if (!$description) $description = $key;

        $value = $this->getKey($key);
        $new = $this->ask("{$description} (current:'{$value}')");

        return !$new || $new === $value ? 0 : $this->setKey($key, $new);
    }

    public function getKey($key)
    {
        $env = file_get_contents($this->env);
        if (!preg_match("/\b{$key}=(.*)/", $env, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    public function setKey($key, $value)
    {
        $env = file_get_contents($this->env);

        $env = preg_replace("/\b{$key}=(.*)/", "{$key}={$value}", $env);

        return file_put_contents($this->env, $env);
    }
}
