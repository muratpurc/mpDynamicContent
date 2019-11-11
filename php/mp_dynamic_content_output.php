<?php
/**
 * Module mpDynamicContent output.
 *
 * @package     Module
 * @subpackage  mpDynamicContent
 * @author      Murat Purç <murat@purc.de>
 * @copyright   Copyright (c) 2013-2019 Murat Purç (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */


// Includes
cInclude('module', 'includes/class.module.mpdynamiccontent.php');

$client = cRegistry::getClientId(true);

// Module configuration
$aModuleConfiguration = array(
    'debug' => false,
    'name' => 'mpDynamicContent',
    'idmod' => $cCurrentModule,
    'container' => $cCurrentContainer,
    'db' => cRegistry::getDb(),
    'cfg' => cRegistry::getConfig(),
    'idart' => cRegistry::getArticleId(true),
    'client' => $client,
    'lang' => cRegistry::getLanguageId(true),
    'idartlang' => cRegistry::getArticleLanguageId(true),
    'isBackendEditMode' => cRegistry::isBackendEditMode(),
    'moduleHandler' => new cModuleHandler($cCurrentModule),
    'propertyColl' => new cApiPropertyCollection($client),
    'type' => 'CMS_TEXT', // The Content-Type which contains the Content-Types structure
    'typeid' => 0,  // Will be set in module!

    // (string) Comma separated list of content types, e. g. 'CMS_HTMLHEAD,CMS_HTML,CMS_IMGEDITOR'
    'supportedContentTypes' => (string) getEffectiveSetting('mp_dynamic_content', 'supported_content_types', 'CMS_HEAD,CMS_HTML,CMS_HTMLHEAD,CMS_IMGEDITOR,CMS_LINKEDITOR'),

    // (string) Comma separated list of not supported content types
    'notSupportedContentTypes' => 'CMS_IMG,CMS_IMGDESCR,CMS_LINK,CMS_LINKTARGET,CMS_LINKDESCR',
);
//##echo "<pre>" . print_r($aModuleConfiguration, true) . "</pre>";

// Create module class instance
$oModule = new ModuleMpDynamicContent($aModuleConfiguration);

$contentTypes = '';
$supportedContentTypes = array();
$allTemplates = array();

// Save content entries only in backend edit mode
if ($oModule->isBackendEditMode) {

    $oModule->saveContentEntries($_POST);

    // Get stored Content-Types content data
    $contentTypes = $oModule->getStoredContentTypeContent();

    // Get all Content-Types
    $supportedContentTypes = $oModule->getSupportedContentTypes();

    // Get all templates
    $allTemplates = $oModule->getTemplates();
}


// Get content type data
$contentTypeData = $oModule->getContentTypeData();
#echo "<pre>\$contentTypeData: " . print_r($contentTypeData, true) . "</pre>";

// Use smarty template to output module code
$tpl = cSmartyFrontend::getInstance();

// NOTE: We have to add the jQuery UI library in backend for CONTENIDO < 4.9.3
$tpl->assign('versionLowerThan493', (-1 === version_compare(CON_VERSION, '4.9.3')));
$tpl->assign('isBackendEditMode', $oModule->isBackendEditMode);
$tpl->assign('backendHtmlPath', cRegistry::getBackendUrl());
$tpl->assign('muid', $oModule->getUid());
$tpl->assign('typeid', $oModule->typeid);

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

?>