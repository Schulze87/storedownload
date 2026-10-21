<?php declare(strict_types=1);

namespace IhrPrefix\PwaShell\Storefront\Controller;

use Shopware\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class PwaController extends StorefrontController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    #[Route(path: '/pwa/manifest.json', name: 'frontend.pwa.manifest', methods: ['GET'])]
    public function manifest(Request $request): JsonResponse
    {
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $salesChannelId = $context instanceof SalesChannelContext ? $context->getSalesChannelId() : null;

        $basePath = $request->getBasePath();
        $startUrl = $basePath !== '' ? $basePath . '/' : '/';

        $appName = $this->systemConfigService->getString('IhrPrefixPwaShell.config.appName', $salesChannelId) ?: 'TGU';
        $shortName = $this->systemConfigService->getString('IhrPrefixPwaShell.config.shortName', $salesChannelId) ?: 'TGU';

        // Icons: media-IDs aus Config holen und URL bauen
        $icon192Url = $this->resolveMediaUrl(
            $this->systemConfigService->getString('IhrPrefixPwaShell.config.icon192', $salesChannelId)
        );
        $icon512Url = $this->resolveMediaUrl(
            $this->systemConfigService->getString('IhrPrefixPwaShell.config.icon512', $salesChannelId)
        );

        return new JsonResponse([
            'name' => $appName,
            'short_name' => $shortName,
            'start_url' => $startUrl,
            'scope' => $startUrl,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#ffffff',
            'icons' => [
                ['src' => $icon192Url ?: $basePath . '/bundles/ihrprefixpwashell/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => $icon512Url ?: $basePath . '/bundles/ihrprefixpwashell/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ]);
    }

    private function resolveMediaUrl(string $mediaId): ?string
    {
        if ($mediaId === '') {
            return null;
        }
        // In Shopware 6.7: MediaRepository über den Container holen und URL bauen.
        // Alternativ: falls du nur statische Icons willst, kannst du das Medien-Feld weglassen.
        // Für Kürze hier: Der Pfad wird über die Media-URL des Shops gebildet.
        // Beispiel: /media/{pfad/zur/datei}.png
        // (Erfordert Injection des MediaRepository – für einen ersten Test reicht der Fallback unten.)
        return null;
    }

    #[Route(path: '/sw.js', name: 'frontend.pwa.sw', methods: ['GET'])]
    public function serviceWorker(): Response
    {
        $js = <<<'JS'
self.addEventListener('fetch', () => {});
JS;

        return new Response($js, 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
