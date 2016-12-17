<?php

use podium\FunctionalTester;

class InstallationCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('podium/install/run');
    }

    public function openInstallPage(FunctionalTester $I)
    {
        $I->seeElement('#drop');
        $I->seeElement('#installPodium');
    }
}
