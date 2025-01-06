<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system.php)
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

use function ougc\InviteSystem\Admin\pluginActivation;
use function ougc\InviteSystem\Admin\pluginDeactivation;
use function ougc\InviteSystem\Admin\pluginInformation;
use function ougc\InviteSystem\Admin\pluginInstallation;
use function ougc\InviteSystem\Admin\pluginIsInstalled;
use function ougc\InviteSystem\Admin\pluginUninstallation;
use function ougc\InviteSystem\Core\addHooks;

use const ougc\InviteSystem\ROOT;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

// You can uncomment the lines below to avoid storing some settings in the DB
define('ougc\InviteSystem\Core\SETTINGS', [
    //'key' => '',
    'newpointsMinimumVersion' => 3100,
]);

define('ougc\InviteSystem\Core\DEBUG', false);

define('ougc\InviteSystem\ROOT', constant('MYBB_ROOT') . 'inc/plugins/ougc/InviteSystem');

require_once ROOT . '/core.php';

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Add our hooks
if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    addHooks('ougc\InviteSystem\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    addHooks('ougc\InviteSystem\Hooks\Forum');
}

require_once ROOT . '/hooks/shared.php';

addHooks('ougc\InviteSystem\Hooks\Shared');

require_once ROOT . '/myalerts.php';

function ougc_invite_system_info(): array
{
    return pluginInformation();
}

function ougc_invite_system_activate(): bool
{
    return pluginActivation();
}

function ougc_invite_system_deactivate(): bool
{
    return pluginDeactivation();
}

function ougc_invite_system_install(): bool
{
    return pluginInstallation();
}

function ougc_invite_system_is_installed(): bool
{
    return pluginIsInstalled();
}

function ougc_invite_system_uninstall(): bool
{
    return pluginUninstallation();
}