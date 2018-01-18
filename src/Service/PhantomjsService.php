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
    private $username = '';

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $companyId = '';

    /**
     * @var int
     */
    private $year = 2017;

    /**
     * @var int
     */
    private $month = 1;

    /**
     * @var string
     */
    private $domain = '';

    /**
     * @return string
     */
    protected function getCommand()
    {
        if ('' === $this->username || '' === $this->password || '' === $this->domain) {
            throw new \InvalidArgumentException('Missing or invalid parameters for PhantomJS command');
        }

        return __DIR__.'/../../bin/phantomjs --cookies-file='
            .__DIR__.'/../../var/phcookies.txt '
            .'--ignore-ssl-errors=true '
            .__DIR__.'/../Resources/phantomjs/script.js '
            .sprintf(
                '%s %s %s %s %s %s',
                $this->username,
                $this->password,
                $this->companyId,
                $this->year,
                $this->month,
                $this->domain
            );
    }

    /**
     * @param string $username
     * @return PhantomjsService
     */
    public function setUsername(string $username): PhantomjsService
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param string $password
     * @return PhantomjsService
     */
    public function setPassword(string $password): PhantomjsService
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $companyId
     * @return PhantomjsService
     */
    public function setCompanyId(string $companyId): PhantomjsService
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * @param int $year
     * @return PhantomjsService
     */
    public function setYear(int $year): PhantomjsService
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param int $month
     * @return PhantomjsService
     */
    public function setMonth(int $month): PhantomjsService
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @param string $domain
     * @return PhantomjsService
     */
    public function setDomain(string $domain): PhantomjsService
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param bool $simulated Return a dummy response if set to TRUE.
     * @throws \Exception
     * @return array
     */
    public function run(bool $simulated = false) : array
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

        $process = new Process($this->getCommand());
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
