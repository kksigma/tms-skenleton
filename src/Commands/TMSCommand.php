<?php

namespace Kksigma\TMS\Commands;

use Illuminate\Console\Command;

class TMSCommand extends Command
{
    public $signature = 'tms';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
