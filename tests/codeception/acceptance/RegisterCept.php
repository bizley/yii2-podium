<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium Register page works');
$I->amOnPage('/podium/register'); 
$I->see('Register', 'li.active a');
$I->see('Registration', 'li.active');
$I->seeElement('form', ['id' => 'register-form']);
