<!-- mp_dynamic_content -->

<div class="mpDynamicContent">

    {if $isBackendEditMode == true}
        {* Load/Output styles only once in current page *}
        {if isset($jQueryUIStylesLoaded) && $jQueryUIStylesLoaded == false}
            {literal}

            <style type="text/css">
                .ui-widget-mp-dynamic-content {font-size: 11px;}
                .ui-dialog .ui-dialog-content.mpDynamicContentDialogForm {position: relative;}
                .ui-dialog .ui-dialog-titlebar-close span {font-size: 0;}
                .ui-state-default {border: none !important;}
                .mpDynamicContent .content_type_sublabel {font-size: 80%; border-top: none; border-right: none;}
                .mpDynamicContentDialogForm .content {height: 100%; border-collapse: collapse; display: table; float: none; width: 100%; background-color: #fff;}
                .mpDynamicContentDialogForm .content hr {margin: 10px 0;}
                .mpDynamicContentDialogForm .content img {vertical-align: middle; cursor: pointer; max-width: none; display: inline;}
                .mpDynamicContentDialogForm h3 {margin: 0.3em 0;}
                .mpDynamicContentDialogForm .contentTypeList th {text-align: left;}
                .mpDynamicContentDialogForm .contentTypeList td {cursor: move; padding: 4px 2px;}
                .mpDynamicContentDialogForm .info {vertical-align: middle; cursor: pointer;}
                .mpDynamicContentDialogForm .text {width: 94px; margin-right: 5px; cursor: auto; background-color: #fff; border: 1px solid #000; line-height: normal;}
                .mpDynamicContentDialogForm .add {margin: 0.6em 0; cursor: pointer; display: inline-block;}
                .mpDynamicContentDialogForm .add img {vertical-align: middle;}
                .mpDynamicContentDialogForm .dynamicContentItem .select {width: 144px; margin-right: 5px;}
                .mpDynamicContentDialogForm .dynamicContentItem .action {vertical-align: middle; cursor: pointer;}
                .mpDynamicContentDialogForm .actionBtn {height: 25px; margin-top: 10px; display: table-row; text-align: right; vertical-align: bottom;}
                .mpDynamicContentDialogForm .actionBtn input {background-color: #ddd; margin-left: 5px;}
            </style>

            {/literal}
        {/if}
        {assign var=jQueryUIStylesLoaded value=true scope="global"}

        <label class="content_type_label ui-helper-clearfix">
            <span style="float:left;">{$LBL_DYNAMIC_CONTENT}</span>
            <span style="float:right;"><img src="{$backendHtmlPath}images/but_edithtml.gif" id="dialog-btn-{$muid}" title="{$BTN_DYNAMIC_CONTENT_TITLE|escape}" style="cursor:pointer"></span>
        </label>

    {/if}

    {foreach from=$contentTypeData item=content}
        {if $content.online == 1}
            {include file="{$content.template}"}
        {/if}
    {/foreach}

    {if $isBackendEditMode == true}

        <div id="dialog-form-{$muid}" class="mpDynamicContentDialogForm" title="{$FRM_TITLE}" style="display:none;">
            <div class="content">
                <h3>{$HDR_BASE_SETTINGS}</h3>
                <label>
                    {$LBL_CONTENT_TYPE_ID}
                    {if $typeid != 0}
                        {assign var="typeidDisabled" value="disabled='disabled'"}
                    {else}
                        {assign var="typeidDisabled" value=""}
                    {/if}
                    <input class="text" type="text" name="contenttypeid-{$muid}" id="contentTypeId-{$muid}" value="{$typeid}" {$typeidDisabled}/>
                </label>
                <img class="info" src="{$backendHtmlPath}images/info.gif" title="{$TITLE_CONTENT_TYPE_ID|escape}" /><br />

                <input type="hidden" name="contenttypes-{$muid}" id="contentTypes-{$muid}" value="{$contentTypes|escape}" />

                <hr />

                <h3>{$HDR_CONTENT_TYPE_SETTINGS}</h3>

                <div class="add" id="addContentType-{$muid}">
                    {$LBL_CONTENT_TYPES}
                    <img src="{$backendHtmlPath}images/but_art_new.gif" title="{$LBL_CONTENT_TYPES|escape}">
                </div>

                <table class="contentTypeList" style="display:none;">
                <tr>
                    <th>{$HDR_LABEL}</th>
                    <th>{$HDR_CONTENT_TYPE}</th>
                    <th>{$HDR_TEMPLATE}</th>
                    <th>{$HDR_ADDITIONAL}</th>
                    <th>&nbsp;</th>
                </tr>
                </table>

                <div class="actionBtn">
                    <input type="button" data-action="ok" value="{$BTN_OK}" />
                    <input type="button" data-action="cancel" value="{$BTN_CANCEL}" />
                </div>
                <br class="ui-helper-clearfix" />
            </div>
        </div>

    {/if}

</div>

{if $isBackendEditMode == true}

<script type="text/javascript">
(function($) {
    function initialize_mpDynamicContent_{$muid}() {
        $(function() {
            var $editBtn = $('#dialog-btn-{$muid}'),
                $template = $('#contentTypesTemplate-{$muid}'),
                $typeId = $('#contentTypeId-{$muid}'),
                $addBtn = $('#addContentType-{$muid}'),
                $types = $('#contentTypes-{$muid}'),
                $editcontentFrm = $('form[name="editcontent"]'),
                $dialog = $('#dialog-form-{$muid}'),
                $dialogActionBtn = $dialog.find('.actionBtn input[type="button"]'),
                $contentTypeList = $dialog.find('.contentTypeList');

            // ####################################################################
            // Functions

            // Helper function to convert JSON to its string representation
            var _jsonStringify = typeof JSON !== 'undefined' ? JSON.stringify : function(obj) {
                var arr = [];
                $.each(obj, function(key, val) {
                    var next = key + ': ';
                    next += $.isPlainObject(val) ? _jsonStringify(val) : val;
                    arr.push(next);
                });
                return '{ ' +  arr.join(', ') + ' }';
            };

            // Dialogs ok action button function
            var _dialogOkAction = function() {
                var entries = {};

                // Loop through all entries and build the data structure
                $contentTypeList.find('.dynamicContentItem').each(function(pos, item) {
                    entries[pos] = {
                        label: $(item).find('input[name="label"]').val(),
                        type: $(item).find('select[name="type"]').val(),
                        template: $(item).find('select[name="template"]').val(),
                        userdefined: $(item).find('input[name="userdefined"]').val(),
                        online: $(item).find('img.action[data-type="online"]').data('value')
                    };
                });

                if (isNaN($typeId.val()) || $typeId.val() < 1) {
                    alert("{$ERR_CONTENT_TYPE_ID}");
                    return;
                }

                // Stringify them and set form fields value
                $types.val(_jsonStringify(entries));
                //##console.log(entries);
                //##console.log(_jsonStringify(entries));

                // Takeover form field to editcontent form and submit the form...
                $typeId.css('visibility', 'hidden');
                $editcontentFrm.append($typeId);
                $editcontentFrm.append($types);
                $editcontentFrm.submit();
            };

            // Dialogs cancel action button function
            var _dialogCancelAction = function() {
                $dialog.dialog('close');
            };

            // Content-Type item online action
            var _onlineAction = function(elem) {
                if (1 == elem.data('value')) {
                    _setOnlineAction(elem, 0);
                } else {
                    _setOnlineAction(elem, 1);
                }
            };

            // Content-Type item online action setter
            var _setOnlineAction = function(elem, value) {
                elem.data('value', value);
                if (1 == value) {
                    elem.attr('src', elem.data('on'));
                    elem.attr('title', "{$LBL_ACTION_ON}");
                } else {
                    elem.attr('src', elem.data('off'));
                    elem.attr('title', "{$LBL_ACTION_OFF}");
                }
            };

            // ####################################################################
            // Event handler

            // Info icon click handler
            $dialog.find('.info').click(function() {
                var title = $(this).attr('title');
                title = title.replace(/\\n/g, "\n");
                alert(title);
            });

            // Edit button click handler, opens the dialog
            $editBtn.click(function() {
                $dialog.dialog({
                    height: 400,
                    width: 550,
                    modal: true,
                    close: function() {
                        return false;
                    }
                }).addClass('ui-widget-mp-dynamic-content');
            });

            // Add button click handler
            $addBtn.click(function() {
                $contentTypeList.show();
                var tpl = $template.html(),
                    node = $(tpl);
                $contentTypeList.append(node);
            });

            // Content-Type list actions click handler
            $contentTypeList.delegate('img.action', 'click', function() {
                var $elem = $(this);
                if ('online' == $elem.data('type')) {
                    _onlineAction($elem);
                }
            });

            // Dialog buttons click handler
            // NOTE: We use own buttons, not the jQuery UI dialog buttons, because of some JS issues!
            $dialogActionBtn.click(function() {
                var action = $(this).data('action');
                if ('ok' === action) {
                    _dialogOkAction();
                } else {
                    _dialogCancelAction();
                }
            });

            // ####################################################################
            // Setup/initialize module UI

            // Initial fill of selected content types
            var types = $.parseJSON($types.val());
            if ('object' === $.type(types) && false === $.isEmptyObject(types)) {
                $contentTypeList.show();
                $.each(types, function(pos, entry) {
                    var tpl = $template.html(),
                        node = $(tpl);
                    node.find('input[name="label"]').val(entry.label);
                    node.find('select[name="type"]').val(entry.type);
                    node.find('select[name="template"]').val(entry.template);
                    node.find('input[name="userdefined"]').val(entry.userdefined);
                    _setOnlineAction(node.find('img.action[data-type="online"]'), entry.online);
                    $contentTypeList.append(node);
                });
            }
        });
    }

    Con.Loader.get(['{$backendHtmlPath}scripts/jquery/jquery-ui.js', '{$backendHtmlPath}styles/jquery/jquery-ui.css'], function() {
        // TODO Murat Purç - Loader may call the callback twice!
        if ($.type(window.bMpDynamicContent_{$muid}_initialized) === 'undefined') {
            window.bMpDynamicContent_{$muid}_initialized = true;
            setTimeout(function() {
                initialize_mpDynamicContent_{$muid}();
            });
        }
    }, window);

})(jQuery);
</script>

<script id="contentTypesTemplate-{$muid}" type="text/x-jQuery-tmpl">
<tr class="dynamicContentItem">
    <td>
        <input class="text" type="text" name="label" value="">
    </td>
    <td>
        <select class="select" name="type">
        {foreach from=$supportedContentTypes item=content}
            <option value="{$content}">{$content}</option>
        {/foreach}
        </select>
    </td>
    <td>
        <select class="select" name="template">
        {foreach from=$allTemplates item=template}
            <option value="{$template.template}">{$template.description}</option>
        {/foreach}
        </select>
    </td>
    <td>
        <input class="text" type="text" name="userdefined" value="">
    </td>
    <td>
        <img class="action" data-type="online" data-value="1" data-on="{$backendHtmlPath}images/online.gif" data-off="{$backendHtmlPath}images/offline.gif" src="{$backendHtmlPath}images/online.gif" title="{$LBL_ACTION_ON|escape}">
    </td>
</tr>
</script>

{/if}

<!-- /mp_dynamic_content -->
