<?php
declare(strict_types=1);

namespace B13\Umami\Controller;

/*
 * This file is part of TYPO3 CMS-based extension umami by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 **/

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class InfoController extends ActionController
{
    const UMAMI_STATISTIC_URL_FIELD = 'umami_statistic_url';

    protected $defaultViewObjectName = BackendTemplateView::class;

    protected function initializeView(ViewInterface $view)
    {
        /* @var BackendTemplateView $view */
        parent::initializeView($view);

        if ($this->actionMethodName === 'showAction') {
            $this->registerDocheaderButtons();
            $view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());
        }
        if ($view instanceof BackendTemplateView) {
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
        }
    }

    public function listAction(): void
    {
        $sites = GeneralUtility::makeInstance(SiteFinder::class)->getAllSites();

        $sitesWithTracking = [];
        foreach ($sites as $site) {
            if (!$site->getConfiguration()[self::UMAMI_STATISTIC_URL_FIELD]) {
                unset($site);
                continue;
            }
            if (!$this->userHasAccessToSite($site->getConfiguration()['rootPageId'])) {
                unset($site);
                continue;
            }

            $sitesWithTracking[$site->getIdentifier()] = $site->getConfiguration();
            $sitesWithTracking[$site->getIdentifier()]['siteName'] = $this->getSitename($site->getConfiguration());
            $sitesWithTracking[$site->getIdentifier()]['identifier'] = $site->getIdentifier();
        }

        if (count($sitesWithTracking) === 1) {
            $this->redirect('show', null, null, ['identifier' => array_key_first($sitesWithTracking)]);
        }

        $this->view->assign('sites', $sitesWithTracking);
    }

    public function showAction(string $identifier): void
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($identifier);

        $this->view->assign('siteName', $this->getSiteName($site->getConfiguration()));
        $this->view->assign('statisticUrl', $site->getConfiguration()[self::UMAMI_STATISTIC_URL_FIELD]);
    }

    private function getSiteName(array $siteConfiguration): string
    {
        if ($siteConfiguration['websiteTitle']) {
            return $siteConfiguration['websiteTitle'];
        }

        $rootPage = GeneralUtility::makeInstance(PageRepository::class)->getPage($siteConfiguration['rootPageId']);

        return $rootPage['title'];
    }

    private function userHasAccessToSite(int $rootPage): bool
    {
        $page = GeneralUtility::makeInstance(PageRepository::class)->getPage($rootPage);

        return $GLOBALS['BE_USER']->doesUserHaveAccess($page, 1);
    }

    public function registerDocHeaderButtons()
    {
        // Get uri builder class and set the request
        $uriBuilder = $this->getUriBuilder();

        $arguments = $this->request->getArguments();

        // Set the uri for the button
        $uri = $uriBuilder->uriFor(
            'list'
        );

        // Get buttonbar container
        /** @var ButtonBar $buttonBar */
        $buttonBar = $this->view->getModuleTemplate()
            ->getDocHeaderComponent()
            ->getButtonBar();

        // Build the button
        $saveButton = $buttonBar->makeLinkButton()
            ->setHref($uri)
            ->setIcon($this->view->getModuleTemplate()
                ->getIconFactory()
                ->getIcon('actions-view-go-back', Icon::SIZE_SMALL))
            ->setTitle(LocalizationUtility::translate('module.action.show.button.back.label', 'umami'))
            ->setShowLabelText(true);

        // Add button to doc header
        $buttonBar->addButton($saveButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
    }

    public function getUriBuilder()
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        return $uriBuilder;
    }
}
