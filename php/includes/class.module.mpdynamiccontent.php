<?php

namespace Purc\MpDynamicContent;

use \cApiArticleLanguage;
use \cApiTypeCollection;
use \cTypeGenerator;
use \stdClass;
use \SplFileObject;

/**
 * CONTENIDO module class for mpDynamicContent
 *
 * @package     CONTENIDO_Modules
 * @subpackage  mpDynamicContent
 * @author      Murat Purç <murat@purc.de>
 * @copyright   Copyright (c) 2013-2019 Murat Purç (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * CONTENIDO module class for mpDynamicContent
 *
 * @property boolean debug
 * @property string name
 * @property int idmod
 * @property int container
 * @property \cDb db
 * @property array cfg
 * @property int idart
 * @property int client
 * @property array clientCfg
 * @property int lang
 * @property int idartlang
 * @property boolean isBackendEditMode
 * @property \cModuleHandler moduleHandler
 * @property \cApiPropertyCollection propertyColl
 * @property string type  The Content-Type
 * @property int typeid
 * @property string[] supportedContentTypes
 * @property string[] notSupportedContentTypes
 */
class Module {

    /**
     * Unique module id (module id + container)
     * @var  string
     */
    protected $_uid = '';

    /**
     * Module properties structure.
     * Not all properties are covered here, some will be added via constructor!
     * @var  array
     */
    protected $_properties = [
        'debug' => false,
        'name' => 'mpDynamicContent',
        'idmod' => 0,
        'container' => 0,
        'db' => null,
        'cfg' => null,
        'client' => 0,
        'lang' => 0,
    ];

    /**
     * Constructor, sets some properties
     * @param array $options Associative options array, entries will be assigned
     *                          to member variables.
     * @throws \cDbException
     * @throws \cException
     */
    public function __construct(array $options) {
        foreach ($options as $k => $v) {
            $this->$k = $v;
        }

        $this->_validate();

        $this->_uid = $this->idmod . '_' . $this->container;

        $this->typeid = (int) $this->_getProperty('typeid');
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name) {
        return (isset($this->_properties[$name])) ? $this->_properties[$name] : null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->_properties[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return (isset($this->_properties[$name]));
    }

    /**
     * @param $name
     */
    public function __unset($name) {
        if (isset($this->_properties[$name])) {
            unset($this->_properties[$name]);
        }
    }

    /**
     * Validates module configuration/data
     */
    protected function _validate() {
        $this->debug = (bool) $this->debug;
        $this->name = (string) $this->name;
        $this->idmod = (int) $this->idmod;
        $this->container = (int) $this->container;
        $this->client = (int) $this->client;
        $this->lang = (int) $this->lang;

        $this->supportedContentTypes = explode(',', $this->supportedContentTypes);
        $this->notSupportedContentTypes = explode(',', $this->notSupportedContentTypes);
    }

    /**
     * Returns the checked attribute sub string usable for checkboxes.
     * @param  string  $name  Configuration item name
     * @return  string
     */
    public function getCheckedAttribute($name) {
        if (isset($this->$name) && '' !== $this->$name) {
            return ' checked="checked"';
        } else {
            return '';
        }
    }

    /**
     * Returns the id attribute value by concatenating passed name with the module uid.
     * @param  string  $name
     * @return  string
     */
    public function getIdValue($name) {
        return $name . '_' . $this->getUid();
    }

    /**
     * Returns the module uid (module id + container).
     * @return  string
     */
    public function getUid() {
        return $this->_uid;
    }

    /**
     * Saves the send content entries data structure
     * @param array $post $_POST array
     * @throws \cDbException
     * @throws \cException
     * @throws \cInvalidArgumentException
     */
    public function saveContentEntries(array $post) {
        $contentTypeIdField = 'contenttypeid-' . $this->getUid();
        if (isset($post[$contentTypeIdField])) {
            $typeid = $post[$contentTypeIdField];
            $this->_setProperty('typeid', $typeid);
            $this->typeid = (int) $typeid;
        }
        $this->_printInfo($this->typeid, 'typeid');

        if (!$this->typeid) {
            return;
        }

        $contentTypesField = 'contenttypes-' . $this->getUid();
        if (isset($post[$contentTypesField])) {
            $contentTypes = (string) $post[$contentTypesField];
            $this->_printInfo($contentTypes, 'saved $contentTypes');
            conSaveContentEntry($this->idartlang, $this->type, $this->typeid, $contentTypes);
        }
    }

    /**
     * Returns the stored content entries data structure
     * @param bool $asObject Flag to convert the structure to a plain object and return it back
     * @return string|object
     * @throws \cDbException
     * @throws \cException
     */
    public function getStoredContentTypeContent($asObject = false) {
        if (!$this->typeid) {
            return ($asObject) ? new stdClass() : '';
        }

        // Get stored data
        $art = new cApiArticleLanguage();
        $art->loadByArticleAndLanguageId($this->idart, $this->lang);
        $content = (string) $art->getContent($this->type, $this->typeid);

        if ($asObject) {
            $content = $this->jsonStringToObject($content);
        }

        return $content;
    }

    /**
     * Converts the given JSON string to a plain object (stdClass)
     * @param  string  $str
     * @return  stdClass
     */
    public function jsonStringToObject($str) {
        if (!empty($str)) {
            $str = @json_decode($str);
            if (!is_object($str)) {
                $str = (object) $str;
            }
        }

        if (!is_object($str)) {
            $str = new stdClass();
        }

        return $str;
    }

    /**
     * Returns list of supported Content-Types.
     * Returns either entries defined in property $this->supportedContentTypes or all Content-Types.
     * @return array
     * @throws \cDbException
     * @throws \cException
     * @throws \cInvalidArgumentException
     */
    public function getSupportedContentTypes() {
        $contentTypes = [];
        $supported = $this->supportedContentTypes;
        $notSupported = $this->notSupportedContentTypes;
        $cApiTypeColl = new cApiTypeCollection();
        $cApiTypeColl->select();
        while ($cApiType = $cApiTypeColl->next()) {
            $type = $cApiType->get('type');
            if (0 === count($supported) && !in_array($type, $notSupported)) {
                $contentTypes[] = $type;
            } elseif (count($supported) > 0 && in_array($type, $supported)) {
                $contentTypes[] = $type;
            }
        }

        return $contentTypes;
    }

    /**
     * Loops through stored Content-Types entries data structure and generates the
     * cms tag for each entry.
     * @return  array
     * @throws \cDbException
     * @throws \cException
     */
    public function getContentTypeData() {
        if (!$this->typeid) {
            return [];
        }

        // Get stored data
        $contentTypes = $this->getStoredContentTypeContent();
        $contentTypesObj = $this->jsonStringToObject($contentTypes);
        $this->_printInfo($contentTypes, 'raw contentTypes');

        $contentTypeData = [];
        // Generate content types
        $typeGen = new cTypeGenerator();
        $counter = 0;
        foreach ($contentTypesObj as $item) {
            $itemData = [
                'typeid' => $this->typeid + $counter,
                'type' => $item->type,
                'value' => stripslashes($typeGen->getGeneratedCmsTag($item->type, $this->typeid + $counter)),
                'label' => $item->label,
                'userdefined' => stripslashes($item->userdefined),
                'online' => isset($item->online) ? $item->online : 1,
                'template' => $this->moduleHandler->getTemplatePath($item->template),
            ];
            $addData = $this->_getAdditionalContentTypeProperties($item->type, $this->typeid + $counter);
            if (count($addData) > 0) {
                $itemData['properties'] = $addData;
            }
            $contentTypeData[] = $itemData;
            $counter++;
        }

        return $contentTypeData;
    }

    /**
     * Returns list templates usable to render a configured Content-Type item.
     * @param  string  $sortBy  Name of property to sort by, either 'description' or 'template'.
     * @return  array
     */
    public function getTemplates($sortBy = 'description') {
        $sortBy = (in_array($sortBy, ['description', 'template'])) ? $sortBy : 'description';
        $templates = [];

        $files = $this->moduleHandler->getAllFilesFromDirectory('template');
        $templatesPath = $this->moduleHandler->getTemplatePath();
        foreach ($files as $file) {
            $parts = pathinfo($file);
            if ('type.' === substr($parts['basename'], 0, 5)) {
                $fileObj = new SplFileObject($templatesPath . $file);
                $line = trim($fileObj->fgets());
                if (!empty($line) && '{*' == substr($line, 0, 2) && '*}' == substr($line, -2)) {
                    // It's a single comment line, use it!
                    $descr = trim(substr(substr($line, 2), 0, -2));
                } else {
                    $descr = $parts['basename'];
                }
                $templates[] = [
                    'template' => $parts['basename'],
                    'description' => $descr
                ];
            }
        }

        // Do the sorting...
        $templates = $this->_sortTemplates($templates, $sortBy);

        return $templates;
    }

    /**
     * Collects additional properties for Content-Types.
     * At the moment it deals only with CMS_IMGEDITOR, gets related CMS_IMG and CMS_IMGDESCR
     * Content-Types, extracts the information and returns them back
     * @param string $type
     * @param string $typeid
     * @return  array
     * @throws \cDbException
     * @throws \cException
     */
    protected function _getAdditionalContentTypeProperties($type, $typeid) {
        $addData = [];

        // Special treatment for Content-Type CMS_IMGEDITOR!
        // We need CMS_IMG and CMS_IMGDESCR by using the same typeid to extract the
        // information about selected image and the description...
        if ('CMS_IMGEDITOR' == $type) {
            $typeGen = new cTypeGenerator();
            $img = $typeGen->getGeneratedCmsTag('CMS_IMG', $typeid);
            if (!empty($img)) {
                $imgDescr = $typeGen->getGeneratedCmsTag('CMS_IMGDESCR', $typeid);
                $file = str_replace($this->clientCfg['upl']['htmlpath'], $this->clientCfg['upl']['path'], $img);
                $dimensions = getimagesize($file);

                $addData = [
                    'src' => $img,
                    'descr' => $imgDescr,
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                ];
            }
        }

        return $addData;
    }

    /**
     * Sorts the template list by the sortBy parameter
     * @param array $templates
     * @param string $sortBy
     * @return array
     */
    protected function _sortTemplates(array $templates, $sortBy) {
        $arrSort = [];
        foreach ($templates as $p => $tpl) {
            $arrSort[$p] = $tpl[$sortBy];
        }
        $arrSort = array_map('strtolower', $arrSort);
        array_multisort($arrSort, SORT_ASC, SORT_STRING, $templates);

        return $templates;
    }

    /**
     * Returns the module property by its name.
     * @param string $name Property name
     * @param string $default Default value
     * @return  mixed
     * @throws \cDbException
     * @throws \cException
     */
    protected function _getProperty($name, $default = '') {
        return $this->propertyColl->getValue('idmod', $this->idmod, 'container_' . $this->container , $name, $default);
    }

    /**
     * Sets the module property by its name.
     * @param string $name Property name
     * @param string $value Value
     * @throws \cDbException
     * @throws \cException
     * @throws \cInvalidArgumentException
     */
    protected function _setProperty($name, $value) {
        $this->propertyColl->setValue('idmod', $this->idmod, 'container_' . $this->container , $name, $value);
    }

    /**
     * Simple debugger, prints preformatted text, if debugging is enabled
     * @param  mixed  $var
     * @param  mixed  $label
     */
    protected function _printInfo($var, $label = '') {
        if (!$this->debug) {
            return;
        }
        if (!empty($label)) {
            $label .= ': ';
        }
        if (is_object($var) || is_array($var)) {
            echo "<pre>{$label}" . print_r($var, true) . "</pre>";
        } else {
            echo "<pre>{$label}{$var}</pre>";
        }
    }
}
