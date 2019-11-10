{* Content-Type CMS_HTML *}
{*
    Template variables:
    - $content.label  Element description
    - $content.userdefined  Text for userdefined usage
    - $content.value  The Content-Type value to render
*}
{if $isBackendEditMode == true && $content.label}
<label class="content_type_label content_type_sublabel">{$content.label}</label>
{/if}
<div class="mpDynamicContentHtml">
{$content.value}
</div>