<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/myalerts/register/init.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow registration to be invitation based, so that new registration require an invitation code for registration.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\InviteSystem\MyAlerts;

use MybbStuff_MyAlerts_Entity_Alert;
use MybbStuff_MyAlerts_Formatter_AbstractFormatter;

use function ougc\InviteSystem\Core\languageLoad;

class RegisterFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
{
    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert): string
    {
        global $lang, $db;

        $uid = $alert->getObjectId();

        $query = $db->simple_select(
            'ougc_invite_system_logs l LEFT JOIN ' . $db->table_prefix . 'ougc_invite_system_codes c ON (l.cid=c.cid)',
            'c.code AS codeString',
            "l.uid='{$uid}'"
        );

        $codeString = $db->fetch_field($query, 'codeString');

        return $lang->sprintf(
            $lang->myalerts_ougc_invite_system_register,
            $outputAlert['from_user'],
            htmlspecialchars_uni($codeString)
        );
    }

    public function init(): bool
    {
        languageLoad();

        return true;
    }

    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert): string
    {
        global $mybb;

        return $mybb->settings['bburl'] . '/' . get_profile_link(
                $alert->getObjectId()
            );
    }
}