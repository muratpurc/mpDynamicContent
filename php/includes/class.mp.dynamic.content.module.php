<?php

/**
 * CONTENIDO module class for mpDynamicContent
 *
 * @package     Module
 * @subpackage  mpDynamicContent
 * @author      Murat Purç
 * @copyright   Murat Purç it-solutions
 * @license     GPL-2.0-or-later
 * @link        https://www.purc.de
 */

use CONTENIDO\Plugin\MpDevTools\Module\AbstractBase;

/**
 * CONTENIDO module class for mpDynamicContent
 *
 * @property cDb db
 * @property int idartlang
 * @property cApiPropertyCollection propertyColl
 * @property string type  The Content-Type
 * @property int typeid
 * @property string[] supportedContentTypes
 * @property string[] notSupportedContentTypes
 */
class MpDynamicContentModule extends AbstractBase
{

    /**
     * Module properties structure.
     * Not all properties are covered here, some will be added via constructor!
     * @var  array
     */
    protected $properties = [
        'db' => null,
        'idartlang' => 0,
        'propertyColl' => null,
        'type' => '',
        'typeid' => 0,
        'supportedContentTypes' => [],
        'notSupportedContentTypes' => [],
    ];

    /**
     * Constructor, sets some properties
     * @param array $properties Properties array
     *
     * @throws cDbException
     * @throws cException
     */
    public function __construct(array $properties)
    {
        parent::__construct('mp_dynamic_content', $properties);

        $this->validate();

        $this->typeid = cSecurity::toInteger($this->_getProperty('typeid'));
    }

    /**
     * Validates module configuration/data
     */
    protected function validate()
    {
        $this->supportedContentTypes = explode(',', $this->supportedContentTypes);
        $this->notSupportedContentTypes = explode(',', $this->notSupportedContentTypes);
    }

    /**
     * Saves the send content entries data structure.
     *
     * @param array $post $_POST array
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function saveContentEntries(array $post)
    {
        $contentTypeIdField = 'contenttypeid-' . $this->getIdentifier();
        if (isset($post[$contentTypeIdField])) {
            $typeid = $post[$contentTypeIdField];
            $this->_setProperty('typeid', $typeid);
            $this->typeid = cSecurity::toInteger($typeid);
        }
        $this->_printInfo($this->typeid, 'typeid');

        if (!$this->typeid) {
            return;
        }

        $contentTypesField = 'contenttypes-' . $this->getIdentifier();
        if (isset($post[$contentTypesField])) {
            $contentTypes = cSecurity::toString($post[$contentTypesField]);
            $this->_printInfo($contentTypes, 'saved $contentTypes');
            conSaveContentEntry($this->idartlang, $this->type, $this->typeid, $contentTypes);
        }
    }

    /**
     * Returns the stored content entries data structure.
     *
     * @param bool $asObject Flag to convert the structure to a plain object and return it back
     * @return string|object
     * @throws cDbException
     * @throws cException
     */
    public function getStoredContentTypeContent(bool $asObject = false)
    {
        if (!$this->typeid) {
            return ($asObject) ? new stdClass() : '';
        }

        // Get stored data
        $art = new cApiArticleLanguage();
        $art->loadByArticleAndLanguageId($this->articleId, $this->languageId);
        $content = (string) $art->getContent($this->type, $this->typeid);

        if ($asObject) {
            $content = $this->jsonStringToObject($content);
        }

        return $content;
    }

    /**
     * Converts the given JSON string to a plain object (stdClass).
     *
     * @param  string|object|stdClass  $str
     * @return  stdClass
     */
    public function jsonStringToObject($str): stdClass
    {
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
     *
     * @return array
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    public function getSupportedContentTypes(): array
    {
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
     * @throws cDbException
     * @throws cException
     */
    public function getContentTypeData(): array
    {
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
                'template' => $this->getModuleHandler()->getTemplatePath($item->template),
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
    public function getTemplates(string $sortBy = 'description'): array
    {
        $sortBy = (in_array($sortBy, ['description', 'template'])) ? $sortBy : 'description';
        $templates = [];

        $files = $this->getModuleHandler()->getAllFilesFromDirectory('template');
        $templatesPath = $this->getModuleHandler()->getTemplatePath();
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
     * Content-Types, extracts the information and returns them back.
     *
     * @param string $type
     * @param int $typeid
     * @return  array
     * @throws cDbException
     * @throws cException
     */
    protected function _getAdditionalContentTypeProperties(string $type, int $typeid): array
    {
        $addData = [];

        // Special treatment for Content-Type CMS_IMGEDITOR!
        // We need CMS_IMG and CMS_IMGDESCR by using the same typeid to extract the
        // information about selected image and the description...
        if ('CMS_IMGEDITOR' == $type) {
            $typeGen = new cTypeGenerator();
            $img = $typeGen->getGeneratedCmsTag('CMS_IMG', $typeid);
            if (!empty($img)) {
                $imgDescr = $typeGen->getGeneratedCmsTag('CMS_IMGDESCR', $typeid);
                $file = str_replace($this->cfgClient[$this->clientId]['upl']['htmlpath'], $this->cfgClient[$this->clientId]['upl']['path'], $img);
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
     * Sorts the template list by the sortBy parameter.
     *
     * @param array $templates
     * @param string $sortBy
     * @return array
     */
    protected function _sortTemplates(array $templates, string $sortBy): array
    {
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
     *
     * @param string $name Property name
     * @param string $default Default value
     * @return  mixed
     * @throws cDbException
     * @throws cException
     */
    protected function _getProperty(string $name, string $default = '')
    {
        return $this->propertyColl->getValue('idmod', $this->moduleId, 'container_' . $this->containerNumber , $name, $default);
    }

    /**
     * Sets the module property by its name.
     *
     * @param string $name Property name
     * @param string $value Value
     * @throws cDbException
     * @throws cException
     * @throws cInvalidArgumentException
     */
    protected function _setProperty(string $name, string $value)
    {
        $this->propertyColl->setValue('idmod', $this->moduleId, 'container_' . $this->containerNumber , $name, $value);
    }

    /**
     * Simple debugger, prints preformatted text, if debugging is enabled.
     *
     * @param  mixed  $var
     * @param  string  $label
     */
    protected function _printInfo($var, string $label = '')
    {
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
