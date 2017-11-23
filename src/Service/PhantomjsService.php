<?php

namespace App\Service;


use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class PhantomjsService
 * @package App\Service
 */
class PhantomjsService
{
    /**
     * @var string
     */
    private $command;

    /**
     * Crawler constructor.
     * @param array $parameters
     */
    function __construct(array $parameters)
    {
        $parameters = implode(' ', $parameters);
        $this->command = __DIR__.'/../../bin/phantomjs --cookies-file='
            .__DIR__.'/../../var/phcookies.txt '
            .__DIR__.'/../Resources/phantomjs/script.js '.$parameters;
    }

    /**
     * @throws ProcessFailedException
     * @return array
     */
    public function run()
    {
        $process = new Process($this->command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $output = json_decode($process->getOutput(), true);

        return is_array($output) ? $output : array();
    }
}