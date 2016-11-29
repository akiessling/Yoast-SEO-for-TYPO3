<?php
namespace YoastSeoForTypo3\YoastSeo\Backend;


use TYPO3\CMS;

class PageLayoutHeader
{

    /**
     * @var string
     */
    const COLUMN_NAME = 'tx_yoastseo_focuskeyword';

    /**
     * @var int
     */
    const FE_PREVIEW_TYPE = 1480321830;

    /**
     * @var CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;

    /**
     * Initialize the page renderer
     */
    public function __construct()
    {
        $this->pageRenderer = CMS\Core\Utility\GeneralUtility::makeInstance(CMS\Core\Page\PageRenderer::class);
    }

    /**
     * @return string
     */
    public function render()
    {
        $lineBuffer = array();

        /** @var CMS\Backend\Controller\PageLayoutController $pageLayoutController */
        $pageLayoutController = $GLOBALS['SOBE'];

        $currentPage = NULL;
        $focusKeyword = '';
        $previewDataUrl = '';
        $recordId = 0;
        $tableName = 'pages';

        if ($pageLayoutController instanceof CMS\Backend\Controller\PageLayoutController
            && (int) $pageLayoutController->id > 0
            && (int) $pageLayoutController->current_sys_language === 0
        ) {
            $currentPage = CMS\Backend\Utility\BackendUtility::getRecord(
                'pages',
                (int) $pageLayoutController->id
            );
        } elseif ($pageLayoutController instanceof CMS\Backend\Controller\PageLayoutController
            && (int) $pageLayoutController->id > 0
            && (int) $pageLayoutController->current_sys_language > 0
        ) {
            $overlayRecords = CMS\Backend\Utility\BackendUtility::getRecordLocalization(
                'pages',
                (int) $pageLayoutController->id,
                (int) $pageLayoutController->current_sys_language
            );

            if (is_array($overlayRecords) && array_key_exists(0, $overlayRecords) && is_array($overlayRecords[0])) {
                $currentPage = $overlayRecords[0];

                $tableName = 'pages_language_overlay';
            }
        }

        if (is_array($currentPage) && array_key_exists(self::COLUMN_NAME, $currentPage)) {
            $focusKeyword = $currentPage[self::COLUMN_NAME];

            $recordId = $currentPage['uid'];

            $previewDataUrl = vsprintf(
                '/index.php?id=%d&type=%d&L=%d',
                array(
                    $pageLayoutController->id,
                    self::FE_PREVIEW_TYPE,
                    $pageLayoutController->current_sys_language
                )
            );
        }

        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/YoastSeo/bundle');

        $this->pageRenderer->addCssFile(
            CMS\Core\Utility\ExtensionManagementUtility::extRelPath('yoast_seo') . 'Resources/Public/CSS/yoast-seo.min.css'
        );

        $lineBuffer[] = '<div id="snippet" ' .
            'data-yoast-focuskeyword="' . htmlspecialchars($focusKeyword) . '"' .
            'data-yoast-previewdataurl="' . htmlspecialchars($previewDataUrl) . '"' .
            'data-yoast-recordtable="' . htmlspecialchars($tableName) . '"' .
            'data-yoast-recordid="' . htmlspecialchars($recordId) . '"' .
            '></div>';

        $lineBuffer[] = '<div class="yoastPanel">';
        $lineBuffer[] = '<h3 class="snippet-editor__heading" data-controls="readability">';
		$lineBuffer[] = '<span class="wpseo-score-icon"></span> Readability <span class="fa fa-chevron-down"></span>';
		$lineBuffer[] = '</h3>';
        $lineBuffer[] = '<div id="readability" class="yoastPanel__content"></div>';
        $lineBuffer[] = '</div>';

        $lineBuffer[] = '<div class="yoastPanel">';
		$lineBuffer[] = '<h3 class="snippet-editor__heading" data-controls="seo">';
        $lineBuffer[] = '<span class="wpseo-score-icon"></span> SEO <span class="fa fa-chevron-down"></span>';
		$lineBuffer[] = '</h3>';
        $lineBuffer[] = '<div id="seo" class="yoastPanel__content"></div>';
        $lineBuffer[] = '</div>';

        return implode(PHP_EOL, $lineBuffer);
    }

}