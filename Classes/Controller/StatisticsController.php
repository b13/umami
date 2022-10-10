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
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Fluid\View\StandaloneView;

class StatisticsController implements RequestHandlerInterface
{
    protected const UMAMI_STATISTICS_URL_FIELD = 'umami_statistic_url';
    protected const MODULE_ROUTE = 'web_umami';

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var StandaloneView
     */
    protected $view;

    /**
     * @var UriBuilder
     */
    protected $uriBuilder;

    /**
     * @var LanguageService
     */
    protected $languageService;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var array<mixed>
     */
    protected $sites = [];

    /**
     * @var array<int>
     */
    protected $userTsPermissions = [];

    public function __construct(
        ModuleTemplate $moduleTemplate,
        UriBuilder $uriBuilder,
        LanguageService $languageService,
        StandaloneView $view,
        SiteFinder $siteFinder,
        PageRepository $pageRepository
    ) {
        $this->moduleTemplate = $moduleTemplate;
        $this->uriBuilder = $uriBuilder;
        $this->languageService = $languageService;
        $this->view = $view;
        $this->siteFinder = $siteFinder;
        $this->pageRepository = $pageRepository;

        $this->initialiseView();
        $this->languageService->includeLLFile('EXT:umami/Resources/Private/Language/locallang.xlf');
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

            $this->view->setTemplate('Show');
            $this->view->assign('site', $site);
            $this->generateMenu($identifierToGet);
        } else {
            $this->view->setTemplate('NoSites');
        }

        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    protected function initialiseView(): void
    {
        $this->view->setLayoutRootPaths(['EXT:umami/Resources/Private/Layouts']);
        $this->view->setTemplateRootPaths(['EXT:umami/Resources/Private/Templates']);
    }

    /**
     * @param array<mixed> $siteConfiguration
     * @return string
     */
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
                !$site->getConfiguration()[self::UMAMI_STATISTICS_URL_FIELD] ||
                !$this->userHasAccessToSite($site->getConfiguration()['rootPageId'])
            ) {
                continue;
            }

            $sitesForModule[$site->getIdentifier()] = [
                'name' => $this->getSitename($site->getConfiguration()),
                'identifier' => $site->getIdentifier(),
                'statisticsUrl' => $site->getConfiguration()[self::UMAMI_STATISTICS_URL_FIELD]
            ];
        }

        $this->sites = $sitesForModule;
    }

    protected function generateMenu(string $currentIdentifier = ''): void
    {
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('SitesWithTracking')->setLabel($this->languageService->getLL('module.dropdown.label'));

        foreach ($this->sites as $site) {
            $item = $menu->makeMenuItem()
                ->setHref(
                    (string)$this->uriBuilder->buildUriFromRoute(
                        self::MODULE_ROUTE,
                        [
                            'action' => 'show',
                            'identifier' => $site['identifier']
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
