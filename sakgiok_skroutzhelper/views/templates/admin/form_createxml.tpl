{** Copyright 2019 Sakis Gkiokas
* This file is part of sakgiok_skroutzhelper module for Prestashop.
*
* Sakgiok_skroutzhelper is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Sakgiok_skroutzhelper is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* For any recommendations and/or suggestions please contact me
* at sakgiok@gmail.com
*
*  @author    Sakis Gkiokas <sakgiok@gmail.com>
*  @copyright 2019 Sakis Gkiokas
*  @license   https://opensource.org/licenses/GPL-3.0  GNU General Public License version 3
*}

<form id="sakgiok_skroutzhelper_generatexml_frm" class="defaultForm form-horizontal" action="{$sakgiok_skroutzhelper_form_action}" method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="sakgiok_skroutzhelper_generatexml" value="1">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s='Generate XML' mod='sakgiok_skroutzhelper'}
        </div>
        <p>
            {l s='Total products:' mod='sakgiok_skroutzhelper'}<span>&nbsp;{$sakgiok_skroutzhelper_total_products}</span>
        </p>
        <p>
            {l s='Products to be indexed:' mod='sakgiok_skroutzhelper'}<span>&nbsp;{$sakgiok_skroutzhelper_indexable_products}</span>
        </p>
        <br/>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='The name of the xml file' mod='sakgiok_skroutzhelper'}
                </label>
                <div class="col-lg-9">
                    <input type="text" id="sakgiok_skroutzhelper_xmlname" name="sakgiok_skroutzhelper_xmlname" />
                </div>
                <label class="control-label col-lg-3">
                    {l s='The language of the products' mod='sakgiok_skroutzhelper'}
                </label>
                <div class="col-lg-9">
                    <select class="form-control fixed-width-xxl" name="sakgiok_skroutzhelper_language" id="sakgiok_skroutzhelper_language">
                        {foreach from=$sakgiok_skroutzhelper_languages key=sakgiok_skroutzhelper_item_key item=sakgiok_skroutzhelper_lang_item}
                            <option value="{$sakgiok_skroutzhelper_lang_item['id_lang']|intval}" {if $sakgiok_skroutzhelper_item_key==0}selected{/if}>{$sakgiok_skroutzhelper_lang_item['name']|escape:'htmlall'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right">
                <i class="process-icon-save"></i>  {l s='Save' mod='sakgiok_skroutzhelper'}
            </button>
        </div>

    </div>


</form>