{* Content-Type CMS_HTMLHEAD *}
{*
    Template variables:
    - $content.label (string) Element description
    - $content.userdefined (string) Text for userdefined usage
    - $content.value (string) The Content-Type value to render
    - $debug (boolean) Debug flag
*}
{if $isBackendEditMode == true}
    {if $content.label}
        <label class="content_type_label content_type_sublabel">- {$content.label}{if $debug} <span class="mpDynamicContentDbg">- {$content.type}[{$content.typeid}]</span>{/if}</label>
    {/if}
    {$content.value}
{else}
    <h2 class="mpDynamicContentHtmlHead">
        {$content.value|strip_tags}
    </h2>
{/if}