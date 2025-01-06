<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/myalerts.php)
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

use MybbStuff_Core_ClassLoader;
use MybbStuff_MyAlerts_AlertFormatterManager;
use MybbStuff_MyAlerts_AlertTypeManager;
use MybbStuff_MyAlerts_Entity_AlertType;

use const GLOB_ONLYDIR;
use const ougc\InviteSystem\ROOT;

function getAvailableLocations(): array
{
    $directory = ROOT . '/myalerts/';

    return array_map(
        'basename',
        glob($directory . '*', GLOB_ONLYDIR)
    );
}

function getInstalledLocations(): array
{
    global $cache;

    return $cache->read('ougc_invite_system_myalerts')['MyAlertLocationsInstalled'] ?? [];
}

function isLocationAlertTypePresent(string $locationName): bool
{
    if (MyAlertsIsIntegrable()) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        return $alertTypeManager->getByCode('ougc_invite_system_' . $locationName) !== null;
    }

    return false;
}

function installLocation(string $name): bool
{
    global $cache;

    $cacheEntry = $cache->read('ougc_invite_system_myalerts');

    if (!in_array($name, $cacheEntry['MyAlertLocationsInstalled'])) {
        $cacheEntry['MyAlertLocationsInstalled'][] = $name;

        $cache->update('ougc_invite_system_myalerts', $cacheEntry);
    }

    if (!isLocationAlertTypePresent($name)) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();

        $alertType->setCode('ougc_invite_system_' . $name);

        $alertTypeManager->add($alertType);
    }

    return true;
}

function uninstallLocation(string $name): bool
{
    global $cache;

    // remove MyAlerts type
    $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

    $alertTypeManager->deleteByCode('ougc_invite_system_' . $name);

    // remove datacache value
    $cacheEntry = $cache->read('ougc_invite_system_myalerts');

    $key = array_search($name, $cacheEntry['MyAlertLocationsInstalled']);

    if ($key !== false) {
        unset($cacheEntry['MyAlertLocationsInstalled'][$key]);

        $cache->update('ougc_invite_system_myalerts', $cacheEntry);
    }

    return true;
}

function initMyAlerts(): bool
{
    defined('MYBBSTUFF_CORE_PATH') || define('MYBBSTUFF_CORE_PATH', MYBB_ROOT . 'inc/plugins/MybbStuff/Core/');

    defined('MYALERTS_PLUGIN_PATH') || define('MYALERTS_PLUGIN_PATH', MYBB_ROOT . 'inc/plugins/MybbStuff/MyAlerts');

    require_once MYBBSTUFF_CORE_PATH . 'ClassLoader.php';

    $classLoader = new MybbStuff_Core_ClassLoader();

    $classLoader->registerNamespace('MybbStuff_MyAlerts', [MYALERTS_PLUGIN_PATH . '/src']);

    $classLoader->register();

    return true;
}

function initLocations(): bool
{
    foreach (getInstalledLocations() as $locationName) {
        require_once ROOT . '/myalerts/' . $locationName . '/init.php';
    }

    return true;
}

function registerMyAlertsFormatters(): bool
{
    global $mybb, $lang, $formatterManager;

    $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

    $formatterManager || $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);

    foreach (getInstalledLocations() as $locationName) {
        $class = '\ougc\InviteSystem\MyAlerts\\' . ucfirst($locationName) . 'Formatter';

        $formatterManager->registerFormatter(new $class($mybb, $lang, 'ougc_invite_system_' . $locationName));
    }

    return true;
}

function MyAlertsIsIntegrable(): bool
{
    global $cache;

    static $status;

    if (!$status) {
        $status = false;

        $plugins = $cache->read('plugins');

        if (!empty($plugins['active']) && in_array('myalerts', $plugins['active'])) {
            if ($euantor_plugins = $cache->read('euantor_plugins')) {
                if (isset($euantor_plugins['myalerts']['version'])) {
                    $version = explode('.', $euantor_plugins['myalerts']['version']);

                    if ($version[0] == '2' && $version[1] > 0) {
                        $status = true;
                    }
                }
            }
        }
    }

    return $status;
}