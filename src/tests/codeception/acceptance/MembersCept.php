<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium Members page works');
$I->amOnPage('/podium/members'); 
$I->see('Members', 'li.active a');
$I->see('Members List', 'li.active');
