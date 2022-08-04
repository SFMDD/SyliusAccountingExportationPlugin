<?php

namespace FMDD\SyliusAccountingExportationPlugin\Controller\Admin;

use FMDD\SyliusAccountingExportationPlugin\Form\Type\PeriodType;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\AdjustmentInterface as BaseAdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface as OrderInterfaceAlias;
use Sylius\Component\Shipping\Model\ShipmentInterface as ShipmentInterfaceAlias;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final class AccountingExportationController extends AbstractController
{
    private OrderRepositoryInterface $orderRepository;
    
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(PeriodType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $channels = $data['channels'];
            $from = $data['from']; $to = $data['to'];

            if ($from > $to) {
                $tmp = $to; $to = $from; $from = $tmp; $data['from'] = $from; $data['to'] = $to;
            }

            Assert::isInstanceOf($from, \DateTimeInterface::class);
            Assert::isInstanceOf($to, \DateTimeInterface::class);

            $orders = $this->getOrdersByPeriod($channels, $from, $to);
            return $this->renderCsv($orders);
        }

        return $this->render('@FMDDSyliusAccountingExportationPlugin/Admin/index.html.twig', [
            'form' => $form->createView(),
            'orders' => 'non submit',
        ]);
    }

    private function renderCsv(array $orders) : Response
    {
        $date = new \DateTime('now');
        $fileName = 'accounting_' .  $date->format("Y-m-d") . '.csv';
        $delimiter = ';';

        $content = 'Order Date;Number;Customer;Total (Tax Included);Total Items (Tax Excluded);Total Shipping (Tax Excluded);Total Taxes;Total Discount;Payment Date' . PHP_EOL;


        /** @var OrderInterface $order */
        foreach($orders as $order) {
            $name = empty($order->getCustomer()->getFullName()) ? $order->getCustomer()->getEmail() : $order->getCustomer()->getFullName();
            $taxIncluded = $this->getIncludedTax($order);
            $taxExcluded = $this->getExcludedTax($order);

            $content .=
                $order->getCheckoutCompletedAt()->format("Y-m-d H:i") . $delimiter .
                '#' . $order->getNumber() . $delimiter .
                $name . $delimiter .
                ($order->getTotal() / 100) . $delimiter .
                (($order->getItemsTotal() - $order->getTaxTotal()) / 100) . $delimiter .
                (($order->getShippingTotal() - $taxIncluded) / 100) . $delimiter .
                (($taxExcluded + $taxIncluded) / 100) . $delimiter .
                ($order->getOrderPromotionTotal() / 100) . $delimiter .
                $order->getLastPayment()->getUpdatedAt()->format("Y-m-d H:i") . PHP_EOL;
        }

        $response = new Response($content);
        $response->headers->set('Content-Encoding', 'UTF-8');
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName);
        return $response;
    }

    private function getOrdersByPeriod($channels, $from, $to): array
    {
        $channelsIds = array_map(function($channel) {return $channel->getCode();}, $channels->toArray());

        return $this->orderRepository
            ->createQueryBuilder('o')
            ->leftJoin('o.channel', 'c')
            ->andWhere('o.state = :state')
            ->andWhere('o.checkoutState = :checkout')
            ->andWhere('o.paymentState = :payment')
            ->andWhere('o.shippingState = :shipping')
            ->andWhere('o.checkoutCompletedAt BETWEEN :from AND :to')
            ->andWhere('c.code IN (:channels)')
            ->setParameter('state', OrderInterfaceAlias::STATE_FULFILLED)
            ->setParameter('checkout', OrderCheckoutStates::STATE_COMPLETED)
            ->setParameter('payment', OrderPaymentStates::STATE_PAID)
            ->setParameter('shipping', ShipmentInterfaceAlias::STATE_SHIPPED)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('channels', $channelsIds)
            ->getQuery()
            ->getResult();
    }


    public function getIncludedTax(OrderInterface $order): int
    {
        return $this->getAmount($order, true);
    }

    public function getExcludedTax(OrderInterface $order): int
    {
        return $this->getAmount($order, false);
    }

    private function getAmount(OrderInterface $order, bool $isNeutral): int
    {
        return array_reduce(
            $order->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT)->toArray(),
            static function (int $total, BaseAdjustmentInterface $adjustment) use ($isNeutral) {
                return $isNeutral === $adjustment->isNeutral() ? $total + $adjustment->getAmount() : $total;
            },
            0
        );
    }
}