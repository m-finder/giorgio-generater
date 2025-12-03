<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;

class ServiceMakeCommand extends GeneratorCommand
{
    /** {@inheritdoc} */
    protected $signature = 'make:service {name} {--p|path=} {--f|force}';

    /** {@inheritdoc} */
    protected $description = 'Command description';

    /** {@inheritdoc} */
    public function handle()
    {
        //
    }

    /** {@inheritdoc} */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }
}
