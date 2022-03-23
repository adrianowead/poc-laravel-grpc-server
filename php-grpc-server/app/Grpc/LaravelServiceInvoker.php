<?php

namespace App\Grpc;

use App\Grpc\Contracts\ServiceInvoker;
use Google\Protobuf\Internal\Message;
use Illuminate\Contracts\Foundation\Application;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Exception\InvokeException;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\StatusCode;

class LaravelServiceInvoker implements ServiceInvoker
{
    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create new Invoker instance
     * 
     * @param   Application     $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;        
    }

    /**
     * @inheritdoc
     */
    public function invoke(
        string $interface,
        Method $method,
        ContextInterface $context,
        ?string $input
    ): string {
        $instance = $this->getApplication()->make($interface);
        $out = $instance->{$method->getName()}($context, $this->makeInput($method, $input));

        try {
            return $out->serializeToString();
        } catch (\Throwable $e) {
            throw new InvokeException(
                message: $e->getMessage(),
                code: StatusCode::INTERNAL,
                previous: $e
            );
        }
    }

    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @param Method $method
     * @param string $body
     * @return Message
     *
     * @throws InvokeException
     */
    private function makeInput(Method $method, ?string $body): Message
    {
        try {
            $class = $method->getInputType();

            /** @var Message $in */
            $in = new $class;
            $in->mergeFromString($body);

            return $in;
        } catch (\Throwable $e) {
            throw new InvokeException(
                message: $e->getMessage(),
                code: StatusCode::INTERNAL,
                previous: $e
            );
        }
    }
}