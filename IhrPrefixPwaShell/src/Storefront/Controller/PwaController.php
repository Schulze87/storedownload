<?php declare(strict_types=1);

namespace IhrPrefix\PwaShell\Storefront\Controller;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $mediaRepository
    ) {
    }

    #[Route(path: '/pwa/manifest.json', name: 'frontend.pwa.manifest', methods: ['GET'])]
    public function manifest(Request $request): JsonResponse
    {
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $salesChannelId = $context instanceof SalesChannelContext ? $context->getSalesChannelId() : null;
        $contextObj = $context instanceof SalesChannelContext ? $context->getContext() : null;

        $basePath = $request->getBasePath();
        $startUrl = $basePath !== '' ? $basePath . '/' : '/';

        $appName = $this->systemConfigService->getString('IhrPrefixPwaShell.config.appName', $salesChannelId) ?: 'TGU';
        $shortName = $this->systemConfigService->getString('IhrPrefixPwaShell.config.shortName', $salesChannelId) ?: 'TGU';

        $icon192Id = $this->systemConfigService->getString('IhrPrefixPwaShell.config.icon192', $salesChannelId);
        $icon512Id = $this->systemConfigService->getString('IhrPrefixPwaShell.config.icon512', $salesChannelId);

        $icon192Url = $this->resolveMediaUrl($icon192Id, $contextObj) ?: $basePath . '/bundles/ihrprefixpwashell/icon-192.png';
        $icon512Url = $this->resolveMediaUrl($icon512Id, $contextObj) ?: $basePath . '/bundles/ihrprefixpwashell/icon-512.png';

        return new JsonResponse([
            'name' => $appName,
            'short_name' => $shortName,
            'start_url' => $startUrl,
            'scope' => $startUrl,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#ffffff',
            'icons' => [
                ['src' => $icon192Url, 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => $icon512Url, 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ]);
    }

    private function resolveMediaUrl(string $mediaId, ?\Shopware\Core\Framework\Context $context): ?string
    {
        if ($mediaId === '' || $context === null) {
            return null;
        }

        $criteria = new Criteria([$mediaId]);
        $media = $this->mediaRepository->search($criteria, $context)->get($mediaId);

        if (!$media instanceof MediaEntity || $media->getUrl() === null) {
            return null;
        }

        return $media->getUrl();
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
