<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium member view page works');
$I->amOnPage('/podium/members/view/1/admin'); 
$I->see('Members', 'li.active a');
$I->see('Members List', 'li');
$I->see('Member View', 'li.active');
$I->see('admin', 'h2');
$I->see('Admin', '.label');
