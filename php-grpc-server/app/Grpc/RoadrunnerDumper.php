<?php

namespace App\Grpc;

use Spiral\Debug\Dumper;
use Spiral\Debug\Renderer\ConsoleRenderer;

class RoadrunnerDumper implements Dumper
{
    /**
     * Dumper instance.
     * 
     * @var \Spiral\Debug\Dumper
     */
    protected $dumper;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        $this->dumper = new Dumper();
        $this->dumper->setRenderer(Dumper::ERROR_LOG, new ConsoleRenderer);
    }

    /**
     * Dump given value.
     * 
     * @param   mixed   $value
     * 
     * @return  void|null
     */
    public function dump($value)
    {
        $this->dumper->dump($value, Dumper::ERROR_LOG);
    }
}