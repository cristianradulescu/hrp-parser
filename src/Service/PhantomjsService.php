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
            .'--ignore-ssl-errors=true '
            .__DIR__.'/../Resources/phantomjs/script.js '.$parameters;
    }

    /**
     * @param bool $simulated Return a dummy response if set to TRUE.
     * @throws \Exception
     * @return array
     */
    public function run(bool $simulated = false)
    {
        $output = $this->getProcessOutput($simulated);
        $outputArray = json_decode($output, true);

        // if the json_decode doesn't return an array, throw the entire output since it should be an error
        if (!is_array($outputArray)) {
            throw new \Exception($output);
        }

        return $outputArray;
    }

    /**
     * @param bool $simulated
     * @return string
     */
    protected function getProcessOutput(bool $simulated = false)
    {
        if ($simulated) {
            /**
             * Warning: the success response file contains data for Oct-2017
             *
             */
            return file_get_contents(__DIR__.'/../Resources/response/hrp-success.json');
        }

        $process = new Process($this->command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
