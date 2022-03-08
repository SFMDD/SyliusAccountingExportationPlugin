<?php

namespace FMDD\SyliusAccountingExportationPlugin\Controller\Admin;

use FMDD\SyliusAccountingExportationPlugin\Form\Type\PeriodType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class AccountingExportationController extends AbstractController
{
    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(PeriodType::class);

        if ($request->request->get($form->getName())) {
            $form->submit($request->request->get($form->getName()));
            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->render('@FMDDSyliusAccountingExportationPlugin/Admin/index.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        // Assert retrieved data
        $data = $form->getData();

        $channels = $data['channels'];
        $from = $data['date'] ?? $data['from'];
        $to = $data['date'] ?? $data['to'];

        // Reverse date if from date greater than end date
        if ($from > $to) {
            $tmp = $to; $to = $from; $from = $tmp; $data['from'] = $from; $data['to'] = $to;
        }

        Assert::isInstanceOf($from, \DateTimeInterface::class);
        Assert::isInstanceOf($to, \DateTimeInterface::class);

        //TODO: Return csv file

        return $this->render('@FMDDSyliusAccountingExportationPlugin/Admin/index.html.twig', [
            'form' => $form->createView(),
            'from' => $from,
            'to' => $to,
        ]);
    }
}