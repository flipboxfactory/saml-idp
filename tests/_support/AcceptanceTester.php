<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions; // do not ever remove this line!

    public function login($username, $password)
    {
        $I = $this;

        $I->amOnPage('/admin/login');
        $I->seeInTitle('Login');
        $I->waitForElement('#login-form', 30);

        $I->fillField('#loginName', $username);
        $I->fillField('#password', $password);
        $I->makeScreenshot();
        $I->click('#submit');
    }
}
