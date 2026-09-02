<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class TestCommand extends Command
{
    protected $signature = 'app:tech-test';

    protected $description = 'Command description';

    public function handle()
    {
        Log::info('Test command executed');
        $this->comment('Test command executed successfully!');
        return Command::SUCCESS;
    }
}
