<?php

namespace EntityGenerator\Util;

class Config
{
    /**
     * @var Config instance
     */
    private static $instance;

    /**
     * @var array database
     */
    private $database;

    /**
     * @var string namespace
     */
    private $namespace;

    /**
     * @var string outputDir
     */
    private $outputDir;

    /**
     * @var string extends
     */
    private $extends;

    /**
     * @var string dateType
     */
    private $dateType = '\\DateTime';

    private function __construct()
    {
        $configFile = ROOT_PATH . '/config.json';

        if (!file_exists($configFile)) {
            throw new \Exception('Config file not found!');
        }

        $config = json_decode(file_get_contents($configFile), true);

        $this->setDatabase($config['database']);
        $this->setOutputDir($config['output-dir']);

        empty($config['namespace']) || $this->setNamespace($config['namespace']);
        empty($config['extends'])   || $this->setExtends($config['extends']);
        empty($config['dateType'])  || $this->setDateType($config['dateType']);
    }

    /**
     * @return Config
     */
    public static function getinstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param array $database
     *
     * @return Config
     */
    public function setDatabase(array $database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     *
     * @return Config
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputDir()
    {
        return $this->outputDir;
    }

    /**
     * @param string $outputDir
     *
     * @return Config
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * @param string $extends
     *
     * @return Config
     */
    public function setExtends($extends)
    {
        $this->extends = $extends;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateType()
    {
        return $this->dateType;
    }

    /**
     * @param string $dateType
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
    }
}