<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Output\BufferedOutput;

trait CallReturn
{
    /**
     * Call another console command, and return the string output.
     *
     * @param  \Symfony\Component\Console\Command\Command|string  $command
     * @param  array  $arguments
     * @param  bool  $return
     * @return string
     */
    public function callReturn($command, array $arguments = [])
    {
        $output = new BufferedOutput();
        $this->runCommand($command, $arguments, $output);

        return $output->fetch();
    }
}
