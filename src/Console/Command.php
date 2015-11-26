<?php

namespace ProAI\Datamapper\Presenter\Console;

use Illuminate\Console\Command;
use ProAI\Datamapper\Metadata\ClassFinder;
use ProAI\Datamapper\Presenter\Metadata\PresenterScanner;
use ProAI\Datamapper\Presenter\Presenter\Repository;
use UnexpectedValueException;

abstract class PresenterCommand extends Command
{
    /**
     * The class finder instance.
     *
     * @var \ProAI\Datamapper\Metadata\ClassFinder
     */
    protected $finder;

    /**
     * The presenter scanner instance.
     *
     * @var \ProAI\Datamapper\Metadata\PresenterScanner
     */
    protected $scanner;

    /**
     * The presenter repository instance.
     *
     * @var \ProAI\Datamapper\Presenter\Repository
     */
    protected $repository;

    /**
     * The config of the datamapper package.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new migration install command instance.
     *
     * @param \ProAI\Datamapper\Metadata\ClassFinder $finder
     * @param \ProAI\Datamapper\Metadata\PresenterScanner $scanner
     * @param \ProAI\Datamapper\Presenter\Repository $schema
     * @param array $config
     * @return void
     */
    public function __construct(ClassFinder $finder, PresenterScanner $scanner, Repository $repository, $config)
    {
        parent::__construct();

        $this->finder = $finder;
        $this->scanner = $scanner;
        $this->repository = $repository;
        $this->config = $config;
    }
}
