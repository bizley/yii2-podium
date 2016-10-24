<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium first forum page works');
$I->amOnPage('/podium/forum/1/1/first-forum'); 
$I->see('Home', 'li a');
$I->see('Main Forum', 'li a');
$I->see('First category', 'li a');
$I->see('First forum', 'li.active');
$I->see('First forum', 'h4.panel-title');
$I->see('First topic', 'a');
