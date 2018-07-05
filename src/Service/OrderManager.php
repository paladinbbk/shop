<?php
/**
 * Created by PhpStorm.
 * User: Texas
 * Date: 05.07.2018
 * Time: 17:34
 */

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\Orders;
use App\Entity\OrderPosition;
use App\Form\OrdersType;
use Symfony\Component\HttpFoundation\Request;

class OrderManager
{
    const SESSION_CART_ID = 'cart';

    private $session;
    private $productRepository;
    private $formFactory;
    private $entityManager;

    public function __construct(
        SessionInterface $session,
        ProductRepository $productRepository,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    public function addOrder(Request $request)
    {
        $order = new Orders();
        $orderForm = $this->formFactory->create(OrdersType::class, $order);
        $orderForm->handleRequest($request);

        if ($orderForm->isSubmitted() && $orderForm->isValid()) {
            $order = $orderForm->getData();
            $this->entityManager->persist($order);

            $cart = $this->session->get(self::SESSION_CART_ID);
            foreach ($cart as $productId => $quantity) {
                $price = $this->productRepository->find($productId)->getPrice();
                $orderPosition = new OrderPosition($price, $quantity, $productId, $order);
                $this->entityManager->persist($orderPosition);
            }
            $this->entityManager->flush();

            return;
        }
    }
}