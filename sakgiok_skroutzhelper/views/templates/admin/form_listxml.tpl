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

<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Files allready generated:' mod='sakgiok_skroutzhelper'}
        <span class="badge">{$sakgiok_skroutzhelper_xml_count}</span>
    </div>
    <div class="row">
        <table class="table">
            <tr>
                <th class="fixed-width-xs center">
                    <span class="title_box">{l s='Num' mod='sakgiok_skroutzhelper'}</span>
                </th>
                <th>
                    <span class="title_box text-left">{l s='File name' mod='sakgiok_skroutzhelper'}</span>
                </th>
                <th class="fixed-width-sm center">
                    <span class="title_box text-left">{l s='Action' mod='sakgiok_skroutzhelper'}</span>
                </th>
            </tr>
            {if $sakgiok_skroutzhelper_xmlnames_arr }
                {$sakgiok_skroutzhelper_counter=0}
                {foreach from=$sakgiok_skroutzhelper_xmlnames_arr item=sakgiok_skroutzhelper_xmlname}
                    {$sakgiok_skroutzhelper_counter=$sakgiok_skroutzhelper_counter+1}
                    <tr>
                        <td class="center">
                            {$sakgiok_skroutzhelper_counter}
                        </td>
                        <td class="text-left">
                            <p>{$sakgiok_skroutzhelper_xmlname['lang']} - <strong>{$sakgiok_skroutzhelper_xmlname['filename']}</strong> (<a href="{$sakgiok_skroutzhelper_baseurl}{$sakgiok_skroutzhelper_xmlname['filename']}" target="_blank">{$sakgiok_skroutzhelper_baseurl}{$sakgiok_skroutzhelper_xmlname['filename']}</a>)</p>
                            <p>Cron job link: <a href="{$sakgiok_skroutzhelper_cronbase}&file_id={$sakgiok_skroutzhelper_xmlname['file_id']}" target="_blank">{$sakgiok_skroutzhelper_cronbase}&file_id={$sakgiok_skroutzhelper_xmlname['file_id']}</a></p>
                        </td>
                        <td>
                            <div class="btn-group-action">
                                <div class="btn-group pull-right">
                                    <a href="{$sakgiok_skroutzhelper_current_url}&amp;sakgiok_skroutzhelper_viewxmlfile=1&amp;sakgiok_skroutzhelper_id_xmlfile={(int)$sakgiok_skroutzhelper_counter-1}}" class="btn btn-default">
                                        <i class="icon-pencil"></i> {l s='View' mod='sakgiok_skroutzhelper'}
                                    </a>
                                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>&nbsp;
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="{$sakgiok_skroutzhelper_current_url}&amp;sakgiok_skroutzhelper_deletexmlfile=1&amp;sakgiok_skroutzhelper_id_xmlfile={(int)$sakgiok_skroutzhelper_counter-1}"
                                               onclick="return confirm('{l s='Do you really want to delete this xml file?' mod='sakgiok_skroutzhelper'}');">
                                                <i class="icon-trash"></i> {l s='Delete' mod='sakgiok_skroutzhelper'}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                {/foreach}
            {/if}
        </table>
    </div>
</div>