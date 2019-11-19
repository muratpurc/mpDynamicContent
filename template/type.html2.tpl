{* Content-Type CMS_HTML 2 *}
{*
    Template variables:
    - $content.label (string) Element description
    - $content.userdefined (string) Text for userdefined usage
    - $content.value (string) The Content-Type value to render
    - $debug (boolean) Debug flag
*}
{if $isBackendEditMode == true && $content.label}
<label class="content_type_label">{$content.label}{if $debug} <span class="mpDynamicContentDbg">- {$content.type}[{$content.typeid}]</span>{/if}</label>
{/if}
<div class="mpDynamicContentHtml2">
{$content.value}
</div>