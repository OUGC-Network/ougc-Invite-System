<p align="center">
    <a href="" rel="noopener">
        <img width="700" height="400" src="https://github.com/user-attachments/assets/a5ce44cc-4bd5-4df3-ae4e-e040cda5cc22" alt="Project logo">
    </a>
</p>

<h3 align="center">ougc Invite System</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/ougc-Invite-System.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/ougc-Invite-System.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Allow registration to be invitation based, so that new registration require an invitation code for registration.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
- [Templates](#templates)
- [Usage](#usage)
    - [Groups](#usage_groups)
    - [Forums](#usage_forums)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Allow registration to be invitation based, so that new registration require an invitation code for registration.

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7
- [Newpoints](https://github.com/OUGC-Network/Newpoints) >= 3.1

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── languages
   │ │ ├── english
   │ │ │ ├── admin
   │ │ │ │ ├── ougc_invite_system.lang.php
   │ │ │ ├── ougc_invite_system.lang.php
   │ ├── plugins
   │ │ ├── ougc
   │ │ │ ├── InviteSystem
   │ │ │ │ ├── admin
   │ │ │ │ │ ├── logs.php
   │ │ │ │ ├── hooks
   │ │ │ │ │ ├── admin.php
   │ │ │ │ │ ├── forum.php
   │ │ │ │ │ ├── shared.php
   │ │ │ │ ├── myalerts
   │ │ │ │ │ ├── register
   │ │ │ │ │ │ ├── init.php
   │ │ │ │ ├── templates
   │ │ │ │ │ ├── .html
   │ │ │ │ │ ├── content.html
   │ │ │ │ │ ├── content_button_deactivate.html
   │ │ │ │ │ ├── content_button_delete.html
   │ │ │ │ │ ├── content_buttons.html
   │ │ │ │ │ ├── content_empty.html
   │ │ │ │ │ ├── content_multipage.html
   │ │ │ │ │ ├── content_points_column.html
   │ │ │ │ │ ├── content_points_thead.html
   │ │ │ │ │ ├── content_row.html
   │ │ │ │ │ ├── current.html
   │ │ │ │ │ ├── filter.html
   │ │ │ │ │ ├── filter_username.html
   │ │ │ │ │ ├── form.html
   │ │ │ │ │ ├── form_additionalgroups.html
   │ │ │ │ │ ├── form_code.html
   │ │ │ │ │ ├── form_email.html
   │ │ │ │ │ ├── form_expire.html
   │ │ │ │ │ ├── form_multiple.html
   │ │ │ │ │ ├── form_points.html
   │ │ │ │ │ ├── form_stock.html
   │ │ │ │ │ ├── form_stock_unlimited.html
   │ │ │ │ │ ├── form_usergroup.html
   │ │ │ │ │ ├── form_usergroup_item.html
   │ │ │ │ │ ├── form_users.html
   │ │ │ │ │ ├── modcp_nav.html
   │ │ │ │ │ ├── register.html
   │ │ │ │ │ ├── register_agreement.html
   │ │ │ │ │ ├── register_validator.html
   │ │ │ │ │ ├── usercp_nav.html
   │ │ │ │ ├── admin.php
   │ │ │ │ ├── core.php
   │ │ │ │ ├── myalerts.php
   │ │ ├── ougc_invite_system.php
   │ ├── tasks
   │ │ ├── ougc_invite_system.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package.
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Newpoints » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Newpoints » Settings_ to manage the plugin settings.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _NewPoints_ to manage Newpoints modules.

### Template Modifications <a name = "template_modifications"></a>

The following templates modifications are required for this plugin.

1. Open the `modcp_nav_users` template, add `<!--OUGC_INVITE_SYSTEM-->` after `{$nav_editprofile}` to add the link to
   the ModCP.
2. Open the `usercp_nav_misc` template, add `<!--OUGC_INVITE_SYSTEM-->` after `{$attachmentop}` to add the link to the
   UserCP.
3. Open the `member_register` template, add `{$ougcInviteSystemRegister}` after `{$referrer}` to add the invite code
   field in the registration page.
4. Open the `member_register_agreement` template, add `{$ougcInviteSystemRegisterAgreement}` before `</form>` to add the
   invite code hidden field in the registration agreement page.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Main Settings

- **Allowed Invite Groups** `select`
    - _Select which groups users can be invited to. Administrator and moderator groups will be ignored. Fallback will
      always be `Registered (2)`._
- **Require Invite Code** `yesno`
    - _If you turn this feature invite codes will be required in registrations._
- **Referral System Integration** `yesno`
    - _If you enable this invite code owners will be assigned as the referral of users._
- **Referral Points Share** `numeric`
    - _If referral integration is enabled, what percent of points does referrers receive? Percentage values only, from
      `0` to `100`. The "Group Rate for Additions" group permission multiplies this._
- **Maximum Registration Attempts** `numeric`
    - _Maximum registration attempts allowed for wrong code submits._
- **Registration Wait Interval** `numeric`
    - _How many minutes users will need to wait after reaching the maximum registration attempts._
- **Per Page Pagination** `numeric`
    - _Maximum items to display per page._
- **Notification Methods** `select`
    - _Select the notification method to use when users are added to private threads._
- **Random Code Characters** `text`
    - _Input which characters to use for generating random codes. Do not change this unless you know what you are doing
      nor leave empty._
- **Random Code Length** `numeric`
    - _Input how many characters to use for generating random codes between `1` and `50`._
- **Renewal Period** `numeric`
    - _Input every how many days users should be granted new codes automatically based off their group permission
      settings._
- **Default Group** `select`
    - _Select the default group for new generated codes._
- **Default Additional Groups** `select`
    - _Select the default additional groups for new generated codes._
- **Default Stock** `select`
    - _Select the default stock for new generated codes._
- **Action Name** `text`
    - _Input the name of the action to be used for the control panel page._

[Go up to Table of Contents](#table_of_contents)

## 📐 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin.

- `ougc_invite_system`
    - _front end_;
- `ougc_invite_system_content`
    - _front end_;
- `ougc_invite_system_content_button_deactivate`
    - _front end_;
- `ougc_invite_system_content_button_delete`
    - _front end_;
- `ougc_invite_system_content_buttons`
    - _front end_;
- `ougc_invite_system_content_empty`
    - _front end_;
- `ougc_invite_system_content_multipage`
    - _front end_;
- `ougc_invite_system_content_points_column`
    - _front end_;
- `ougc_invite_system_content_points_thead`
    - _front end_;
- `ougc_invite_system_content_row`
    - _front end_;
- `ougc_invite_system_current`
    - _front end_;
- `ougc_invite_system_filter`
    - _front end_;
- `ougc_invite_system_filter_username`
    - _front end_;
- `ougc_invite_system_form`
    - _front end_;
- `ougc_invite_system_form_additionalgroups`
    - _front end_;
- `ougc_invite_system_form_code`
    - _front end_;
- `ougc_invite_system_form_email`
    - _front end_;
- `ougc_invite_system_form_expire`
    - _front end_;
- `ougc_invite_system_form_multiple`
    - _front end_;
- `ougc_invite_system_form_points`
    - _front end_;
- `ougc_invite_system_form_stock`
    - _front end_;
- `ougc_invite_system_form_stock_unlimited`
    - _front end_;
- `ougc_invite_system_form_usergroup`
    - _front end_;
- `ougc_invite_system_form_usergroup_item`
    - _front end_;
- `ougc_invite_system_form_users`
    - _front end_;
- `ougc_invite_system_modcp_nav`
    - _front end_;
- `ougc_invite_system_register`
    - _front end_;
- `ougc_invite_system_register_agreement`
    - _front end_;
- `ougc_invite_system_register_validator`
    - _front end_;
- `ougc_invite_system_usercp_nav`
    - _front end_;

[Go up to Table of Contents](#table_of_contents)

## 📖 Usage <a name="usage"></a>

The following is a description of additional configuration for this plugin.

### Groups <a name="usage_groups"></a>

2 new settings are added to groups.

- **Can purchase sticky status for threads?**
- **Sticky Market Rate** _This works as a percentage. So "0" = user does not pay anything "100" = users pay full
  price, "200" = user pays twice the price, etc._

1 new settings are added to forums.

### Forums <a name="usage_forums"></a>

- **Sticky Market Limit**

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/ougc-Invite-System/contributors) who participated in
this project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)