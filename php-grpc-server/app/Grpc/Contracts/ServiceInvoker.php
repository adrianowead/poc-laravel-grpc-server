<?php

namespace App\Grpc\Contracts;

use Illuminate\Contracts\Foundation\Application;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Method;

interface ServiceInvoker
{
    /**
     * Invoke service.
     * 
     * @param   string                                     $interface
     * @param   \Spiral\RoadRunner\GRPC\Method             $method
     * @param   \Spiral\RoadRunner\GRPC\ContextInterface   $context
     * @param   string                                     $input
     * 
     * @return  string
     */
    public function invoke(
        string $interface,
        Method $method,
        ContextInterface $context,
        ?string $input
    ): string;

    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication(): Application;
}