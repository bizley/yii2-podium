<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium first category page works');
$I->amOnPage('/podium/category/1/first-category'); 
$I->see('Home', 'li a');
$I->see('Main Forum', 'li a');
$I->see('First category', 'li.active');
$I->see('First category', 'h4.panel-title');
$I->see('First forum', 'a');
$I->see('First topic', 'a');
