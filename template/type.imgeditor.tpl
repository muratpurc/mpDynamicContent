{* Content-Type CMS_IMGEDITOR *}
{*
    Template variables:
    - $content.label (string) Element description
    - $content.userdefined (string) Text for userdefined usage
    - $content.value (string) The Content-Type value to render. NOTE: This values is used only in backend
        to display the editor. It is recommended to use $content.properties to render the image tag!
    - $content.properties (array) Additional properties for CMS_IMGEDITOR, contains addional data about
        selected image as follows:
    - $content.properties.src (string) The URL to the image
    - $content.properties.descr (string) Image description, could be used for title or alt attribute
    - $content.properties.width (int) Image with
    - $content.properties.height (int) Image height
    - $debug (boolean) Debug flag
*}
{if $isBackendEditMode == true && $content.label}
    <label class="content_type_label content_type_sublabel">- {$content.label}{if $debug} <span class="mpDynamicContentDbg">- {$content.type}[{$content.typeid}]</span>{/if}</label>
{/if}

{if isset($content.properties) && $content.properties|@count > 0}
    <div class="mpDynamicContentImgeditor"><img src="{$content.properties.src}" alt="{$content.properties.descr|escape}" /></div>
{/if}
{if $isBackendEditMode == true}
    {$content.value}
{/if}

