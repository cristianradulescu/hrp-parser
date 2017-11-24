<?php

namespace App\Controller;

use App\Service\PhantomjsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="default")
     */
    public function index()
    {
        return $this->render(
            'index.html.twig',
            [
                'title' => 'HRP parser',
                'label_user' => 'User',
                'label_password' => 'Password',
                'label_company' => 'Company',
                'label_year' => 'Year',
                'label_month' => 'Month',
                'label_submit' => 'Export'
            ]
        );
    }

    /**
     * @Route("/export", name="export", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function export(Request $request)
    {
        $params = $request->request->all();
        $params[] = $this->getParameter('hrp_domain');

        try {
            $phantomjsService = new PhantomjsService($params);
            $output = $phantomjsService->run();
        } catch (ProcessFailedException $pe) {

        }

        return new Response('ok');
    }
}
