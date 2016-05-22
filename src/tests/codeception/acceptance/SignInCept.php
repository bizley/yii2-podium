<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium Sign in page works');
$I->amOnPage('/podium/login'); 
$I->see('Sign in', 'li.active a');
$I->see('Sign in', 'li.active');
$I->seeElement('form', ['id' => 'login-form']);
