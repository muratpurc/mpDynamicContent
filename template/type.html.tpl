{* Content-Type CMS_HTML *}
{*
    Template variables:
    - $content.label (string) Element description
    - $content.userdefined (string) Text for userdefined usage
    - $content.value (string) The Content-Type value to render
    - $debug (boolean) Debug flag
*}
{if $isBackendEditMode == true && $content.label}
    <label class="content_type_label content_type_sublabel">- {$content.label}{if $debug} <span class="mpDynamicContentDbg">- {$content.type}[{$content.typeid}]</span>{/if}</label>
{/if}
<div class="mpDynamicContentHtml">
    {$content.value}
</div>