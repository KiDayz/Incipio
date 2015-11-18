<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\TresoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UrssafController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            ->add('date', 'genemu_jquerydate', array('label' => 'Nombre de développeur au :', 'required' => true, 'widget' => 'single_text', 'data' => date_create(), 'format' => 'dd/MM/yyyy'))
            ->getForm();

        $RMs = array();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            $data = $form->getData();

            //$RMs = $em->getRepository('mgateSuiviBundle:Mission')->findBy(array('$debutOm' => 1));

            $qb = $em->createQueryBuilder();
            $qb->select('m')
                ->from('mgateSuiviBundle:Mission', 'm')
                ->where('m.debutOm <= :date')
                ->orderBy('m.finOm', 'DESC')
                //->andWhere('m.finOm >= :date')
                ->setParameters(array('date' => $data['date']));

            $RMs = $qb->getQuery()->getResult();
        }

        return $this->render('mgateTresoBundle:Urssaf:index.html.twig', array('form' => $form->createView(), 'RMs' => $RMs));
    }
}