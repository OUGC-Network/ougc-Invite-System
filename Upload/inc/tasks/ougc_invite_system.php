<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/tasks/ougc_invite_system.php)
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

use function ougc\InviteSystem\Core\expireCodes;
use function ougc\InviteSystem\Core\executeTask;

function task_ougc_invite_system(array &$taskData): array
{
    global $lang, $plugins;

    expireCodes();

    executeTask();

    if (is_object($plugins)) {
        $taskData = $plugins->run_hooks('task_ougc_invite_system', $taskData);
    }

    add_task_log($taskData, $lang->ougc_invite_system_task_ran);

    return $taskData;
}
