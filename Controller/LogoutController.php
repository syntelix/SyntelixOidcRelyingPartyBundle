<?php

namespace Syntelix\Bundle\OidcRelyingPartyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LogoutController.
 */
class LogoutController extends Controller
{
    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function logoutAction(Request $request)
    {
        return $this->get('syntelix_oidc_rp.logout')->logout($request);
    }
}
