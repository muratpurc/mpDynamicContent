{* Content-Type CMS_HTMLHEAD *}
{*
    Template variables:
    - $content.label  Element description
    - $content.userdefined  Text for userdefined usage
    - $content.value  The Content-Type value to render
*}
{if $isBackendEditMode == true}
{if $content.label}
    <label class="content_type_label content_type_sublabel">{$content.label}</label>
{/if}
{$content.value}
{else}
<h2 class="mpDynamicContentHtmlHead">
{$content.value|strip_tags}
</h2>
{/if}