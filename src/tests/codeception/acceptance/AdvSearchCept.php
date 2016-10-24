<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium advanced search page works');
$I->amOnPage('/podium/search'); 
$I->see('Search Forum', 'li.active');
$I->seeElement('form', ['id' => 'search-form']);
