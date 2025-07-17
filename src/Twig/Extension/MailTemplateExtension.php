<?php declare(strict_types=1);

namespace TigerMedia\TigerConnect\Twig\Extension;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailTemplateExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('tiger_connect_render', [$this, 'tigerConnectRender'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @param Environment $twig
     * @param string $content
     * @param mixed[] $data
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function tigerConnectRender(Environment $twig, string $content, array $data = []): string
    {
        $template = $twig->createTemplate($content);
        return $template->render($data);
    }
}