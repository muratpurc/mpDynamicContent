<?php
/**
 * CONTENIDO module class for mpDynamicContent
 *
 * @package     CONTENIDO_Modules
 * @subpackage  mpDynamicContent
 * @author      Murat Purc <murat@purc.de>
 * @copyright   Copyright (c) 2013-2019 Murat Purc (http://www.purc.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html - GNU General Public License, version 2
 */

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * CONTENIDO module class for mpDynamicContent
 */
class ModuleMpDynamicContent {


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
    protected $_properties = array(
        'debug' => false,
        'name' => 'mpDynamicContent',
        'idmod' => 0,
        'container' => 0,
        'db' => null,
        'cfg' => null,
        'client' => 0,
        'lang' => 0,
    );

    /**
     * Module translations
     * @var  array
     */
    protected $_i18n = array();

    /**
     * Constructor, sets some properties
     * @param  array  $options  Associative options array, entries will be assigned
     *                          to member variables.
     * @param  array  $translations  Associative translations list
     */
    public function __construct(array $options, array $translations = array()) {

        foreach ($options as $k => $v) {
            $this->$k = $v;
        }

        $this->_validate();

        $this->_i18n = $translations;
        $this->_uid = $this->idmod . '_' . $this->container;

        $this->typeid = (int) $this->propertyColl->getValue('idmod', $this->idmod, 'container_' . $this->container , 'typeid', '');
    }

    /**
     * Magic getter, see PHP doc...
     */
    public function __get($name) {
        return (isset($this->_properties[$name])) ? $this->_properties[$name] : null;
    }

    /**
     * Magic setter, see PHP doc...
     */
    public function __set($name, $value) {
        $this->_properties[$name] = $value;
    }

    /**
     * Magic method, see PHP doc...
     */
    public function __isset($name) {
        return (isset($this->_properties[$name]));
    }

    /**
     * Magic method, see PHP doc...
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
     * @param string $name Configuration item name
     * @return string
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
     * @param string $name
     * @return string
     */
    public function getIdValue($name) {
        return $name . '_' . $this->getUid();
    }

    /**
     * Returns the module uid (module id + container).
     * @return string
     */
    public function getUid() {
        return $this->_uid;
    }

    /**
     * Saves the send content entries data structure
     * @param  array  $post  $_POST array
     */
    public function saveContentEntries(array $post) {
        $contentTypeIdField = 'contenttypeid-' . $this->getUid();
        if (isset($post[$contentTypeIdField])) {
            $typeid = $post[$contentTypeIdField];
            $this->propertyColl->setValue('idmod', $this->idmod, 'container_' . $this->container , 'typeid', $typeid);
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
     * Returns the previous stored content entries data structure
     * @return  string
     */
    public function getStoredContentTypeContent() {
        if (!$this->typeid) {
            return '';
        }

        // Get stored data
        $art = new cApiArticleLanguage();
        $art->loadByArticleAndLanguageId($this->idart, $this->lang);
        $content = (string) $art->getContent($this->type, $this->typeid);
        return $content;
    }

    /**
     * Returns list of supported Content-Types.
     * Returns either entries defined in property $this->supportedContentTypes or all Content-Types.
     * @return  array
     */
    public function getSupportedContentTypes() {
        $contentTypes = array();

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
     */
    public function getContentTypeData() {
        if (!$this->typeid) {
            return array();
        }

        // Get stored data
        $contentTypes = $this->getStoredContentTypeContent();

        if (!empty($contentTypes)) {
            $contentTypes = @json_decode($contentTypes);
            if (!is_object($contentTypes)) {
                $contentTypes = (object) $contentTypes;
            }
        }

        $contentTypeData = array();
        // Generate content types
        $typeGen = new cTypeGenerator();
        $counter = 0;
        foreach ($contentTypes as $item) {
            $itemData = array(
                'typeid' => $this->typeid + $counter,
                'type' => $item->type,
                'value' => stripslashes($typeGen->getGeneratedCmsTag($item->type, $this->typeid + $counter)),
                'label' => $item->label,
                'userdefined' => stripslashes($item->userdefined),
                'online' => isset($item->online) ? $item->online : 1,
                'template' => $this->moduleHandler->getTemplatePath($item->template),
            );
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
        $sortBy = (in_array($sortBy, array('description', 'template'))) ? $sortBy : 'description';
        $templates = array();

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
                $templates[] = array(
                    'template' => $parts['basename'],
                    'description' => $descr
                );
            }
        }

        // Do the sorting...
        $arrSort = array();
        foreach ($templates as $p => $tpl) {
            $arrSort[$p] = $tpl[$sortBy];
        }
        $arrSort = array_map('strtolower', $arrSort);
        array_multisort($arrSort, SORT_ASC, SORT_STRING, $templates);

        return $templates;
    }

    /**
     * Collects additional properties for Content-Types.
     * At the moment it deals only with CMS_IMGEDITOR, gets related CMS_IMG and CMS_IMGDESCR
     * Content-Types, extracts the information and returns them back
     * @param string $type
     * @param string $typeid
     * @return array
     */
    protected function _getAdditionalContentTypeProperties($type, $typeid) {
        $addData = array();

        // Special treatment for Content-Type CMS_IMGEDITOR!
        // We need CMS_IMG and CMS_IMGDESCR by using the same typeid to extract the
        // information about selected image and the description...
        if ('CMS_IMGEDITOR' == $type) {
            $typeGen = new cTypeGenerator();
            $img = $typeGen->getGeneratedCmsTag('CMS_IMG', $typeid);
            if (!empty($img)) {
                $imgDescr = $typeGen->getGeneratedCmsTag('CMS_IMGDESCR', $typeid);

                $clientCfg = cRegistry::getClientConfig($this->client);
                $file = str_replace($clientCfg['upl']['htmlpath'], $clientCfg['upl']['path'], $img);
                $dimensions = getimagesize($file);

                $addData = array(
                    'src' => $img,
                    'descr' => $imgDescr,
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                );
            }
        }

        return $addData;
    }

    /**
     * Simple debugger, print preformatted text, if debugging is enabled
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
