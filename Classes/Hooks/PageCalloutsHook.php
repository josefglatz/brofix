<?php

declare(strict_types=1);
namespace Sypets\Brofix\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Sypets\Brofix\Repository\BrokenLinkRepository;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\InfoboxViewHelper;

final class PageCalloutsHook
{
    /**
     * @var BrokenLinkRepository
     */
    private $brokenLinkRepository;

    public function __construct()
    {
        $this->brokenLinkRepository = GeneralUtility::makeInstance(BrokenLinkRepository::class);
    }

    /**
     * Create flash message for showing information about broken links in page module
     *
     * @param mixed[] $pageInfo
     * @return array{'title'?: string, 'message'?: string, 'state'?: int}:
     */
    public function addMessages(array $pageInfo): array
    {
        if (!$pageInfo || !is_array($pageInfo)) {
            return [];
        }
        $pageId = (int)($pageInfo['uid']);
        if ($pageId === 0) {
            return [];
        }
        $lang = $this->getLanguageService();

        // todo: access check
        if ($this->brokenLinkRepository->hasPageBrokenLinks($pageId)) {
            $title = '';
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uri = (string)$uriBuilder->buildUriFromRoute('web_info', ['id' => $pageId]) . '&SET[function]=Sypets\\Brofix\\View\\BrofixReport';
            $message = $lang->sL('LLL:EXT:brofix/Resources/Private/Language/locallang.xlf:broken_links_found_for_page');
            //$message .= '<p><a class="btn btn-info" href="javascript:top.goToModule(\'web_info\',1, \'SET[function]=Sypets%5C%5CBrofix%5C%5CView%5C%5CBrofixReport\');">';
            $message .= '<p><a class="btn btn-info" href="' . $uri . '">';
            $message .= $lang->sL('LLL:EXT:brofix/Resources/Private/Language/locallang.xlf:go_to_info_modul')
                . '</a> '
                . $lang->sL('LLL:EXT:brofix/Resources/Private/Language/locallang.xlf:and_check_links')
                . '!</p>';
            return [
                'title' => $title,
                'message' => $message,
                'state' => InfoboxViewHelper::STATE_ERROR
            ];
        }
        return [];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
