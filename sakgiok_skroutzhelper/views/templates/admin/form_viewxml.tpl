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
        {l s='Contents of' mod='sakgiok_skroutzhelper'}<span>&nbsp;{$sakgiok_skroutzhelper_filepath|escape:'htmlall'}</span>
    </div>
    <div class="row">
        <div>
            <textarea readonly="true" style="border: none;background-color:white;height: 600px;font-family: monospace ,sans-serif;font-size: 14px;cursor: text;">{$sakgiok_skroutzhelper_filecontents}</textarea>
        </div>
    </div>
</div>