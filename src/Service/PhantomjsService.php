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
    function __construct(array $parameters = array())
    {
        $parameters = implode(' ', $parameters);
        $this->command = __DIR__.'/../../bin/phantomjs --cookies-file='
            .__DIR__.'/../../var/phcookies.txt '
            .__DIR__.'/../Resources/phantomjs/script.js '.$parameters;
    }

    /**
     * @param bool $simulated By default return a dummy response
     * @throws ProcessFailedException
     * @return array
     */
    public function run(bool $simulated = true)
    {
        if ($simulated) {
            $jsonContent = file_get_contents(__DIR__.'/../Resources/response/hrp.json');
            return json_decode($jsonContent, true);
        }

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