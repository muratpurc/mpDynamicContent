{* Content-Type CMS_IMGEDITOR *}
{*
    Template variables:
    - $content.label  Element description
    - $content.userdefined  Text for userdefined usage
    - $content.value  The Content-Type value to render. NOTE: This values is used only in backend
                      to display the editor. It is recommended to use $content.properties to render
                      the image tag!
    - $content.properties  Additional properties for CMS_IMGEDITOR, contains addional data about
        selected image as follows:
      - $content.properties.src  The URL to the image
      - $content.properties.descr  Image description, could be used for title or alt attribute
      - $content.properties.width  Image with
      - $content.properties.height  Image height
*}
{if $isBackendEditMode == true && $content.label}
    <label class="content_type_label content_type_sublabel">{$content.label}</label>
{/if}
<div class="mpDynamicContentImgeditor">
{if $content.properties|@count > 0}
    <img src="{$content.properties.src}" alt="{$content.properties.descr|escape}" width="{$content.properties.width}" height="{$content.properties.height}" />
{/if}
{if $isBackendEditMode == true}
    {$content.value}
{/if}
</div>
