{* Content-Type CMS_HTML 2 *}
{*
    Template variables:
    - $content.userdefined  Text for userdefined usage
    - $content.label  Element description
    - $content.value  The Content-Type value to render
*}
{if $isBackendEditMode == true && $content.label}
<label class="content_type_label">{$content.label}</label>
{/if}
<div class="mpDynamicContentHtml2">
{$content.value}
</div>