<?php
/**
 * PHP wrapper class for SoX sound excnange library
 *
 * @see http://sox.sourceforge.net/
 * @author Aravind
 */
class Sox
{
    protected $options = [
        'channel' => 'c',
        'sampleRate' => 'r',
    ];
    /**
     * Constructor
     * @throws Exception if Sox library is not present in shell
     */
    public function __construct()
    {
        if (!$this->checkIfSoxExists()) {
            throw new Exception('[Sox Error] Sox convertor not found.');
        }
    }

    /**
     * Check if Sox exists in system
     *
     * @return bool $success
     */
    protected function checkIfSoxExists()
    {
        $result = shell_exec('sox --version');

        return (!empty($result));
    }

    /**
     * Fetch duration of audio file in seconds
     *
     * @param string $filename Audio file name
     * @return float $duration Duration in seconds
     */
    public function getAudioDurationInSeconds($filename)
    {
        $duration = $this->soxi('-D ' . $filename);

        if (!is_numeric(trim($duration))) {
            return 0;
        }

        $duration = floatval($duration);

        return round($duration, 2);
    }

    /**
     * Fetch sample rate of audio file
     *
     * @param string $filename Audio file name
     * @return int $sampleRate
     */
    public function getAudioSampleRate($filename)
    {
        $sampleRate = $this->soxi('-r ' . $filename);

        if (!is_numeric(trim($sampleRate))) {
            return 0;
        }

        return (int)$sampleRate;
    }

    /**
     * Execute soxi command
     *
     * @param string $cmd command
     * @return string $result
     */
    protected function soxi($cmd)
    {
        $cmd = escapeshellcmd($cmd);

        return shell_exec('soxi ' . $cmd);
    }

    /**
     * Convert audio file
     *
     * @param string $infile From filename
     * @param string $outfile To Filename
     * @param array $options - ['infile' => [infile options], 'outfile' => [outile options], 'effects' => effects string]
     * @return void
     * @throws \Exception
     */
    public function convert($infile, $outfile, $options = [])
    {
        if (!file_exists($infile)) {
            throw new Exception('[Sox Error] File to convert by sox does not exists.');
        }

        if (file_exists($outfile)) {
            throw new Exception('[Sox Error] Converted file already exists');
        }

        $infileOptions = (!empty($options['infile'])) ? $this->getOptionsAsCommand($options['infile']) : '';
        $outfileOptions = (!empty($options['outfile'])) ? $this->getOptionsAsCommand($options['outfile']) : '';
        $effects = (!empty($options['effects'])) ? $options['effects'] : '';

        $cmd = $infileOptions . ' ' . $infile . $outfileOptions . ' ' . $outfile . ' ' . $effects;

        $result = $this->sox($cmd);

        if (!file_exists($outfile)) {
            throw new Exception("[Sox Error] Error in converting audio file");
        }
    }

    /**
     * Get file options
     *
     * @param string $key key
     * @return string $optionShorthand
     */
    protected function getOption($key)
    {
        if (!empty($this->options[$key])) {
            return $this->options[$key];
        }
    }

    /**
     * Execute sox command
     *
     * @param string $cmd Command
     * @return string|void result
     */
    protected function sox($cmd)
    {
        $cmd = escapeshellcmd($cmd);

        return shell_exec('sox ' . $cmd);
    }

    /**
     * Convert options to string command
     *
     * @param array $options options
     * @return string $result
     */
    protected function getOptionsAsCommand($options)
    {
        $cmd = '';

        foreach ($options as $key => $value) {
            $option = $this->getOption($key);
            if (!empty($option)) {
                $cmd .= ' -' . $option . ' ' . $value . ' ';
            }
        }

        return $cmd;
    }
}
