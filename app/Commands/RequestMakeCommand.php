<?php

namespace App\Commands;

use Illuminate\Console\GeneratorCommand;

class RequestMakeCommand extends GeneratorCommand
{
    /** {@inheritdoc} */
    protected $signature = 'make:request {name} {--p|path=} {--f|force}';

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
