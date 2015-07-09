<?php
namespace EntityGenerator\Generator;

abstract class Template
{
    /**
     * @var \Twig_Environment twig
     */
    protected $twig;

    public function __construct()
    {
        \Twig_Autoloader::register();

        $loader = new \Twig_Loader_Filesystem(ROOT_PATH . '/templates');
        $this->twig = new \Twig_Environment($loader);
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}