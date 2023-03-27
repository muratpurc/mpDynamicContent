<?php
/**
 * Module mpDynamicContent output.
 *
 * @package     Module
 * @subpackage  mpDynamicContent
 * @author      Murat Purç
 * @copyright   Murat Purç it-solutions
 * @license     GPL-2.0-or-later
 * @link        https://www.purc.de
 */

(function() {

    // ########################################################################
    // ########## Initialization/Settings

    if (!class_exists(\CONTENIDO\Plugin\MpDevTools\Module\AbstractBase::class)) {
        new cException('This module requires the plugin "Mp Dev Tools", please download, install and activate it!');
    }

    // Includes
    if (!class_exists(MpDynamicContentModule::class)) {
        cInclude('module', 'includes/class.mp.dynamic.content.module.php');
    }

    $client = cRegistry::getClientId(true);

    // Create module class instance
    $module = new MpDynamicContentModule([
        'debug' => false,
        'db' => cRegistry::getDb(),
        'idartlang' => cRegistry::getArticleLanguageId(true),
        'propertyColl' => new cApiPropertyCollection($client),
        'type' => 'CMS_TEXT', // The Content-Type which contains the Content-Types structure
        'typeid' => 0,  // Will be set in module!

        // (string) Comma separated list of content types, e. g. 'CMS_HTMLHEAD,CMS_HTML,CMS_IMGEDITOR'
        'supportedContentTypes' => (string) getEffectiveSetting('module_mp_dynamic_content', 'supported_content_types', 'CMS_HEAD,CMS_HTML,CMS_HTMLHEAD,CMS_IMGEDITOR,CMS_LINKEDITOR'),

        // (string) Comma separated list of not supported content types
        'notSupportedContentTypes' => 'CMS_IMG,CMS_IMGDESCR,CMS_LINK,CMS_LINKTARGET,CMS_LINKDESCR',
    ]);

    $contentTypes = '';
    $supportedContentTypes = [];
    $allTemplates = [];

    // Save content entries only in backend edit mode
    if ($module->isBackendEditMode()) {

        $module->saveContentEntries($_POST);

        // Get stored Content-Types content data
        $contentTypes = $module->getStoredContentTypeContent();

        // Get all Content-Types
        $supportedContentTypes = $module->getSupportedContentTypes();

        // Get all templates
        $allTemplates = $module->getTemplates();
    }


    // Get content type data
    $contentTypeData = $module->getContentTypeData();
    #echo "<pre>\$contentTypeData: " . htmlspecialchars(print_r($contentTypeData, true)) . "</pre>";

    // Use smarty template to output module code
    $tpl = cSmartyFrontend::getInstance();

    $tpl->assign('isBackendEditMode', $module->isBackendEditMode());
    $tpl->assign('backendHtmlPath', cRegistry::getBackendUrl());
    $tpl->assign('muid', $module->getIdentifier());
    $tpl->assign('typeid', $module->typeid);
    $tpl->assign('debug', $module->debug);

    $tpl->assign('LBL_DYNAMIC_CONTENT', mi18n("LBL_DYNAMIC_CONTENT"));
    $tpl->assign('BTN_DYNAMIC_CONTENT_TITLE', mi18n("BTN_DYNAMIC_CONTENT_TITLE"));
    $tpl->assign('HDR_BASE_SETTINGS', mi18n("HDR_BASE_SETTINGS"));
    $tpl->assign('LBL_CONTENT_TYPE_ID', mi18n("LBL_CONTENT_TYPE_ID"));
    $tpl->assign('TITLE_CONTENT_TYPE_ID', mi18n("TITLE_CONTENT_TYPE_ID"));
    $tpl->assign('LBL_CONTENT_TYPES', mi18n("LBL_CONTENT_TYPES"));
    $tpl->assign('BTN_OK', mi18n("BTN_OK"));
    $tpl->assign('BTN_CANCEL', mi18n("BTN_CANCEL"));
    $tpl->assign('FRM_TITLE', mi18n("FRM_TITLE"));
    $tpl->assign('HDR_CONTENT_TYPE_SETTINGS', mi18n("HDR_CONTENT_TYPE_SETTINGS"));
    $tpl->assign('HDR_LABEL', mi18n("HDR_LABEL"));
    $tpl->assign('HDR_CONTENT_TYPE', mi18n("HDR_CONTENT_TYPE"));
    $tpl->assign('HDR_TEMPLATE', mi18n("HDR_TEMPLATE"));
    $tpl->assign('HDR_ADDITIONAL', mi18n("HDR_ADDITIONAL"));
    $tpl->assign('HDR_ACTIONS', mi18n("HDR_ACTIONS"));
    $tpl->assign('LBL_ACTION_ON', mi18n("LBL_ACTION_ON"));
    $tpl->assign('LBL_ACTION_OFF', mi18n("LBL_ACTION_OFF"));
    $tpl->assign('ERR_CONTENT_TYPE_ID', mi18n("ERR_CONTENT_TYPE_ID"));

    $tpl->assign('contentTypes', $contentTypes);
    $tpl->assign('contentTypeData', $contentTypeData);
    $tpl->assign('supportedContentTypes', $supportedContentTypes);
    $tpl->assign('allTemplates', $allTemplates);

    $tpl->display('mp_dynamic_content.tpl');

})();

?>