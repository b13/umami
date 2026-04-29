<?php

declare(strict_types=1);

namespace B13\Umami\Controller;

/*
 * This file is part of TYPO3 CMS-based extension umami by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

#[AsController]
class StatisticsController implements RequestHandlerInterface
{
    protected const UMAMI_STATISTICS_URL_FIELD = 'umami_statistic_url';
    protected const MODULE_ROUTE = 'insights_umami';

    protected ModuleTemplate $moduleTemplate;
    protected LanguageService $languageService;

    public function __construct(
        protected ModuleTemplateFactory $moduleTemplateFactory,
        protected UriBuilder $uriBuilder,
        protected SiteFinder $siteFinder,
        protected PageRepository $pageRepository,
        protected LanguageServiceFactory $languageServiceFactory,
        protected array $sites = [],
        /**
         * @var array<int>
         */
        protected array $userTsPermissions = [],
    ) {
        $this->languageService = $this->languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER']);
        if ($GLOBALS['BE_USER']->getTSConfig()['umami.']['allowedRootPages'] ?? false) {
            $this->userTsPermissions = array_map(
                'intval',
                explode(',', $GLOBALS['BE_USER']->getTSConfig()['umami.']['allowedRootPages'])
            );
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->buildSites();
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);
        if (count($this->sites) > 0) {
            if ($request->getQueryParams()['identifier'] ?? false) {
                $identifierToGet = $request->getQueryParams()['identifier'];
                $GLOBALS['BE_USER']->pushModuleData(self::MODULE_ROUTE, ['identifier' => $identifierToGet]);
            } else {
                $identifierToGet = (string)($GLOBALS['BE_USER']->getModuleData(self::MODULE_ROUTE)['identifier'] ?? '');
            }

            if ($identifierToGet !== '' && array_key_exists($identifierToGet, $this->sites)) {
                $site = $this->sites[$identifierToGet];
            } else {
                $site = reset($this->sites);
            }

            $this->moduleTemplate->assign('site', $site);
            $this->generateMenu($identifierToGet);
            return $this->moduleTemplate->renderResponse('Statistics/Show');
        }
        return $this->moduleTemplate->renderResponse('Statistics/NoSites');

    }

    protected function getSiteName(array $siteConfiguration): string
    {
        if ($siteConfiguration['websiteTitle'] ?? false) {
            return $siteConfiguration['websiteTitle'];
        }

        $rootPage = $this->pageRepository->getPage($siteConfiguration['rootPageId']);
        return $rootPage['title'];
    }

    protected function userHasAccessToSite(int $rootPage): bool
    {
        $page = $this->pageRepository->getPage($rootPage);

        return $GLOBALS['BE_USER']->doesUserHaveAccess($page, Permission::PAGE_SHOW)
            || in_array($page['uid'], $this->userTsPermissions, true);
    }

    protected function buildSites(): void
    {
        $sites = $this->siteFinder->getAllSites();

        $sitesForModule = [];
        foreach ($sites as $site) {
            if (
                !($site->getConfiguration()[self::UMAMI_STATISTICS_URL_FIELD] ?? false) ||
                !$this->userHasAccessToSite($site->getConfiguration()['rootPageId'])
            ) {
                continue;
            }

            $sitesForModule[$site->getIdentifier()] = [
                'name' => $this->getSitename($site->getConfiguration()),
                'identifier' => $site->getIdentifier(),
                'statisticsUrl' => $site->getConfiguration()[self::UMAMI_STATISTICS_URL_FIELD],
            ];
        }

        $this->sites = $sitesForModule;
    }

    protected function generateMenu(string $currentIdentifier = ''): void
    {
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('SitesWithTracking')->setLabel($this->languageService->sL('LLL:EXT:umami/Resources/Private/Language/locallang.xlf:module.dropdown.label'));

        foreach ($this->sites as $site) {
            $item = $menu->makeMenuItem()
                ->setHref(
                    (string)$this->uriBuilder->buildUriFromRoute(
                        self::MODULE_ROUTE,
                        [
                            'action' => 'show',
                            'identifier' => $site['identifier'],
                        ]
                    )
                )
                ->setTitle($site['name']);

            if ($currentIdentifier === $site['identifier']) {
                $item->setActive(true);
            }

            $menu->addMenuItem($item);
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }
}
