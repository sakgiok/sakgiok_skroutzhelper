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

{literal}
    <script>
        (function (a, b, c, d, e, f, g) {
            a['SkroutzAnalyticsObject'] = e;
            a[e] = a[e] || function () {
                (a[e].q = a[e].q || []).push(arguments);
            };
            f = b.createElement(c);
            f.async = true;
            f.src = d;
            g = b.getElementsByTagName(c)[0];
            g.parentNode.insertBefore(f, g);
        })(window, document, 'script', 'https://analytics.skroutz.gr/analytics.min.js', 'skroutz_analytics');
    {/literal}
        skroutz_analytics('session', 'connect', '{$sk_shop_id}');
</script>