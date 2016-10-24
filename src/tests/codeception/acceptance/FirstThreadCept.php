<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium first thread page works');
$I->amOnPage('/podium/thread/1/1/1/first-topic'); 
$I->see('Home', 'li a');
$I->see('Main Forum', 'li a');
$I->see('First category', 'li a');
$I->see('First forum', 'li a');
$I->see('First topic', 'li.active');
$I->see('First topic', 'h4');
$I->see('First post', '.popover-content');
