<?php
declare(strict_types=1);
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BusinessException
 * Date: 2023-01-09 09:12
 * Update: 2023-01-09 09:12
 */

namespace Rickytech\Library\Exceptions;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Rickytech\Library\Response\Result1;
use Throwable;

class BusinessException extends ExceptionHandler
{
    protected $logger;
    private Result1 $response;

    public function __construct(ContainerInterface $container, Result1 $response)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('exception');
        $this->response = $response;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $formatter = ApplicationContext::getContainer()->get(FormatterInterface::class);
        if ($throwable instanceof BusinessException) {
            return $this->response->fail($throwable->getCode, $throwable->getMessage());
        }
        if ($throwable instanceof HttpException) {
            return $this->response->fail($throwable->getStatusCode(), $throwable->getMessage());
        }
        $this->logger->error($formatter->format($throwable));
        return $this->response->fail(500, env('APP_ENV') === 'dev' ? $throwable->getMessage() : 'Server Error');

    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}