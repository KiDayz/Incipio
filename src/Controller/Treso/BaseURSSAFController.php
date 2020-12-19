<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Treso;

use App\Entity\Treso\BaseURSSAF;
use App\Form\Treso\BaseURSSAFType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseURSSAFController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_BaseURSSAF_index", path="/Tresorerie/BasesURSSAF", methods={"GET","HEAD"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $bases = $em->getRepository(BaseURSSAF::class)->findAll();

        return $this->render('Treso/BaseURSSAF/index.html.twig', ['bases' => $bases]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     * @Route(name="treso_BaseURSSAF_ajouter", path="/Tresorerie/BaseURSSAF/Ajouter", methods={"GET","HEAD","POST"}, defaults={"id": "-1"})
     * @Route(name="treso_BaseURSSAF_modifier", path="/Tresorerie/BaseURSSAF/Modifier/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function modifier(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$base = $em->getRepository(BaseURSSAF::class)->find($id)) {
            $base = new BaseURSSAF();
        }

        $form = $this->createForm(BaseURSSAFType::class, $base);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($base);
                $em->flush();

                return $this->redirectToRoute('treso_BaseURSSAF_index', []);
            }
        }

        return $this->render('Treso/BaseURSSAF/modifier.html.twig', [
                    'form' => $form->createView(),
                    'base' => $base,
                ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="treso_BaseURSSAF_supprimer", path="/Tresorerie/BaseURSSAF/Supprimer/{id}", methods={"GET","HEAD","POST"}, requirements={"id": "\d+"})
     *
     * @return RedirectResponse
     */
    public function supprimer(BaseURSSAF $base)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($base);
        $em->flush();

        return $this->redirectToRoute('treso_BaseURSSAF_index', []);
    }
}
