<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium frontpage works');
$I->amOnPage('/'); 
$I->see('Home', 'li.active a');
$I->see('Main Forum', 'li.active');
