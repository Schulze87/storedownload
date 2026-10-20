<?php declare(strict_types=1);

namespace IhrPrefix\PwaShell\Storefront\Controller;

use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class PwaController extends StorefrontController
{
    #[Route(path: '/pwa/manifest.json', name: 'frontend.pwa.manifest', methods: ['GET'])]
    public function manifest(Request $request): JsonResponse
    {
        $basePath = $request->getBasePath();
        $startUrl = $basePath !== '' ? $basePath . '/' : '/';

        return new JsonResponse([
            'name' => 'TGU',
            'short_name' => 'TGU',
            'start_url' => $startUrl,
            'scope' => $startUrl,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#ffffff',
            'icons' => [
                [
                    'src' => $basePath . '/bundles/ihrprefixpwashell/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => $basePath . '/bundles/ihrprefixpwashell/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);
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
