<?php

use tests\codeception\AcceptanceTester;

$I = new AcceptanceTester($scenario);

$I->wantTo('ensure that Podium Moderation Team page works');
$I->amOnPage('/podium/members/mods'); 
$I->see('Members', 'li.active a');
$I->see('Moderation Team', 'li.active');
