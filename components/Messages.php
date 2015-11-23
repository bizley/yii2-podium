<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

/**
 * Podium Messages
 * Constants with messages strings.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Messages
{
    
    const ACCESS_ROLES_CREATED = 'Access roles have been created.';
    const ACCESS_ROLES_CREATING_ERROR = 'Error during access roles creating';
    const ACCOUNT_CREATING_ERROR = 'Error during account creating';
    const ACTION_CALL_MISSING = 'Installation aborted! Action call missing.';
    const ADMINISTRATOR_ACCOUNT_CREATED = 'Administrator account has been created.';
    const ADMINISTRATOR_PRIVILEGES_SET = 'Administrator privileges have been set for the user of ID {id}.';
    const CANNOT_FIND_INSTALLATION_STEP = 'Installation aborted! Can not find the requested installation step.';
    const CANNOT_FIND_INSTALLATION_STEPS = 'Installation aborted! Can not find the installation steps.';
    const COLUMN_NAME_MISSING = 'Installation aborted! Column name missing.';
    const COLUMN_TYPE_MISSING = 'Installation aborted! Column type missing.';
    const CONFIG_DEFAULT_SETTINGS_ADDED = 'Config default settings have been added.';
    const CONTENT_ADDED = 'Default Content has been added.';
    const CONTENT_ADDING_ERROR = 'Error during content adding';
    const DATABASE_SCHEMA_MISSING = 'Installation aborted! Database schema missing.';
    const DATABASE_TABLE_NAME_MISSING = 'Installation aborted! Database table name missing.';
    const DROPPING_TABLES = 'Please wait... Dropping tables.';
    const EMAIL_NEW_TITLE = 'New e-mail activation link at {forum}';
    const EMAIL_NEW_BODY = '<p>{forum} New E-mail Address Activation</p><p>To activate your new e-mail address open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>Thank you<br />{forum}</p>';
    const EMAIL_PASS_TITLE = '{forum} password reset link';
    const EMAIL_PASS_BODY = '<p>{forum} Password Reset</p><p>You are receiving this e-mail because someone has started the process of changing the account password at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
    const EMAIL_REACT_TITLE = '{forum} account reactivation';
    const EMAIL_REACT_BODY = '<p>{forum} Account Activation</p><p>You are receiving this e-mail because someone has started the process of activating the account at {forum}.<br />If this person is you open the following link in your Internet browser and follow the instructions on screen.</p><p>{link}</p><p>If it was not you just ignore this e-mail.</p><p>Thank you!<br />{forum}</p>';
    const EMAIL_REG_TITLE = 'Welcome to {forum}! This is your activation link';
    const EMAIL_REG_BODY = '<p>Thank you for registering at {forum}!</p><p>To activate you account open the following link in your Internet browser:<br />{link}<br /></p><p>See you soon!<br />{forum}</p>';
    const EMAIL_SUB_TITLE = 'New post in subscribed thread at {forum}';
    const EMAIL_SUB_BODY = '<p>There has been new post added in the thread you are subscribing. Click the following link to read the thread.</p><p>{link}</p><p>See you soon!<br />{forum}</p>';
    const FOREIGN_KEY_NAME_MISSING = 'Installation aborted! Foreign key name missing.';
    const FOREIGN_KEY_REFERENCE_MISSING = 'Installation aborted! Foreign key reference missing.';
    const INDEX_COLUMNS_MISSING = 'Installation aborted! Index columns missing.';
    const INDEX_NAME_MISSING = 'Installation aborted! Index name missing.';
    const MAINTENANCE_WARNING = 'Podium is currently in the Maintenance mode. All users without Administrator privileges are redirected to {maintenancePage}. You can switch the mode off at {settingsPage}.';
    const NEW_COLUMN_NAME_MISSING = 'Installation aborted! New column name missing.';
    const NEW_TABLE_NAME_MISSING = 'Installation aborted! New table name missing.';
    const NO_ADMIN_ID_SET = '{userComponent} is set to \'{inheritParam}\' but no administrator ID has been set with {adminId} parameter. Administrator privileges will not be set.';
    const NO_ADMINISTRATOR_PRIVILEGES_SET = 'No administrator privileges have been set.';
    const PAGE_MAINTENANCE = 'Maintenance page';
    const PAGE_SETTINGS = 'Settings page';
    const PREPARING_FOR_INSTALLATION = 'Podium post database table not found - preparing for installation';
    const REFERENCED_COLUMNS_MISSING = 'Installation aborted! Referenced columns missing.';
    const SETTINGS_ADDING_ERROR = 'Error during settings adding';
    const TABLE_COLUMN_ADDED = 'Table column {name} has been added';
    const TABLE_COLUMN_ADDING_ERROR = 'Error during table column {name} adding';
    const TABLE_COLUMN_DROPPED = 'Table column {name} has been dropped';
    const TABLE_COLUMN_DROPPING_ERROR = 'Error during table column {name} dropping';
    const TABLE_COLUMN_RENAMED = 'Table column {name} has been renamed to {new}';
    const TABLE_COLUMN_RENAMING_ERROR = 'Error during table column {name} renaming to {new}';
    const TABLE_COLUMN_UPDATED = 'Table column {name} has been updated';
    const TABLE_COLUMN_UPDATING_ERROR = 'Error during table column {name} updating';
    const TABLE_CREATED = 'Table {name} has been created';
    const TABLE_CREATING_ERROR = 'Error during table {name} creating';
    const TABLE_DROPPED = 'Table {name} has been dropped.';
    const TABLE_DROPPING_ERROR = 'Error during table {name} dropping';
    const TABLE_FOREIGN_KEY_ADDED = 'Table foreign key {name} has been added';
    const TABLE_FOREIGN_KEY_ADDING_ERROR = 'Error during table foreign key {name} adding';
    const TABLE_FOREIGN_KEY_DROPPED = 'Table foreign key {name} has been dropped';
    const TABLE_FOREIGN_KEY_DROPPING_ERROR = 'Error during table foreign key {name} dropping';
    const TABLE_INDEX_ADDED = 'Table index {name} has been added';
    const TABLE_INDEX_ADDING_ERROR = 'Error during table index {name} adding';
    const TABLE_INDEX_DROPPED = 'Table index {name} has been dropped';
    const TABLE_INDEX_DROPPING_ERROR = 'Error during table index {name} dropping';
    const TABLE_RENAMED = 'Table {name} has been renamed to {new}';
    const TABLE_RENAMING_ERROR = 'Error during table {name} renaming to {new}';
    const TERMS_TITLE = 'Forum Terms and Conditions';
    const TERMS_BODY  = 'Please remember that we are not responsible for any messages posted. We do not vouch for or warrant the accuracy, completeness or usefulness of any post, and are not responsible for the contents of any post.<br /><br />The posts express the views of the author of the post, not necessarily the views of this forum. Any user who feels that a posted message is objectionable is encouraged to contact us immediately by email. We have the ability to remove objectionable posts and we will make every effort to do so, within a reasonable time frame, if we determine that removal is necessary.<br /><br />You agree, through your use of this service, that you will not use this forum to post any material which is knowingly false and/or defamatory, inaccurate, abusive, vulgar, hateful, harassing, obscene, profane, sexually oriented, threatening, invasive of a person\'s privacy, or otherwise violative of any law.<br /><br />You agree not to post any copyrighted material unless the copyright is owned by you or by this forum.';
}
