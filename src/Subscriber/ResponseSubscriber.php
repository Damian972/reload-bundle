<?php

namespace Damian972\ReloadBundle\Subscriber;

use Symfony\Bridge\Twig\DataCollector\TwigDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Twig\Environment;

class ResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TwigDataCollector
     */
    private $twigDataCollector;

    /**
     * @var int
     */
    private $serverPort;

    public function __construct(Profiler $profiler, Environment $twig, int $serverPort)
    {
        $this->twig = $twig;
        $this->twigDataCollector = $profiler->get('twig');
        $this->serverPort = $serverPort;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$event->isMasterRequest() || $response->isRedirection()
        || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
        || 'html' !== $request->getRequestFormat()
        || $request->isXmlHttpRequest()
        || false !== stripos($response->headers->get('Content-Disposition'), 'attachment;')) {
            return;
        }

        $this->injectScript($response);
    }

    private function injectScript(Response $response): void
    {
        $content = $response->getContent();
        $position = strripos($content, '</body>');
        $templates = array_keys($this->twigDataCollector->getTemplates());

        if (false !== $position) {
            $template = $this->twig->render('@Reload/template.html.twig', [
                'templates' => json_encode($templates),
                'server_port' => $this->serverPort,
            ]);
            $content = substr($content, 0, $position).$template.substr($content, $position);
            $response->setContent($content);
        }
    }
}
