<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Players;
use App\Entity\Orders;
use App\Entity\Teams;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;

class MarketController extends AbstractController
{
  private $doctrine;
  private $paginator;
  public function __construct(ManagerRegistry $doctrine, PaginatorInterface $paginator,)
  {
    $this->doctrine = $doctrine;
    $this->paginator = $paginator;
  }

  #[Route('/market-place', name: 'app_market_place')]
  public function market(Request $request): Response
  {
    $playerQuery = $this->doctrine->getManager()->getRepository(Players::class)
      ->findAllTeam();
    $pagination = $this->paginator->paginate(
      $playerQuery, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      6 /*limit per page*/
    );
    return $this->render('market/index.html.twig', [
      'error'         => '',
      'players' => $pagination
    ]);
  }

  #[Route('/order/{id}', name: 'app_place_order')]
  public function placeOrder(Request $request, int $id): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $error='';
    $playerQuery = $this->doctrine->getManager()->getRepository(Players::class)
      ->findOneJoinTeam($id);
    if (isset($_POST['submit'])) {
    if($this->getUser()->getTeamId()===$playerQuery[0]->getTeamId()){
      $error="You cannot buy this player because it exist within your team";

    }else{
      $entityManager = $this->doctrine->getManager();
      $player = new Orders();
      $player->setPlayerId($id);
      $player->setTeamId($playerQuery[0]->getTeamId());
      $player->setInterestedTeam($this->getUser()->getTeamId());
      $player->setOfferAmount(is_numeric($request->request->get('_offerAmount')) ? $request->request->get('_offerAmount') : 0);
      $player->setStatus(1);
      $entityManager->persist($player);
      $entityManager->flush();
      return $this->redirectToRoute('app_my_order');
    }
    }
    return $this->render('market/order.html.twig', [
      'error'         => '',
      'err'         => $error,
      'player' => $playerQuery,
      'moneyBalance' =>  $this->doctrine->getManager()->getRepository(Teams::class)->find($this->getUser()->getTeamId())->getBalance()
    ]);
  }

  #[Route('/my-order', name: 'app_my_order')]
  public function myOrder(Request $request, ManagerRegistry $doctrine): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    $awaitingPurchaseOrder = $this->doctrine->getManager()->getRepository(Orders::class)
      ->findByInterestTeam($this->getUser()->getTeamId());
    $awaitingSalesOrder = $this->doctrine->getManager()->getRepository(Orders::class)
      ->findByTeam($this->getUser()->getTeamId());
    $entityManager = $doctrine->getManager();
    if (isset($_POST['submit'])) {
      $playerOrder = $this->doctrine->getRepository(Orders::class)->find($request->request->get('id'));
      $team_id = $playerOrder->getTeamId();
      $sellerAccount = $this->doctrine->getRepository(Teams::class)->find(2);
      $sellerAccount->setBalance($sellerAccount->getBalance() + $playerOrder->getOfferAmount());
      $entityManager->persist($sellerAccount);
      $entityManager->flush();
      $buyAccount = $this->doctrine->getRepository(Teams::class)->find($playerOrder->getInterestedTeam());
      $buyAccount->setBalance($buyAccount->getBalance() - $playerOrder->getOfferAmount());
      $entityManager->persist($buyAccount);
      $entityManager->flush();

      $entityManager->getRepository(Orders::class)->UpdateSales($playerOrder->getPlayerId(), $playerOrder->getInterestedTeam());

      return $this->redirectToRoute('app_my_order');
    }

    if (isset($_POST['delete'])) {
      $player = $entityManager->getRepository(Orders::class)->find($request->request->get('id'));
      if (!$player) {
        throw $this->createNotFoundException(
          'No article found for id ' . $request->request->get('id')
        );
      }
      $entityManager->remove($player);
      $entityManager->flush();
      return $this->redirectToRoute('app_my_order');
    }

    return $this->render('market/buyer_order.html.twig', [
      'error'         => '',
      'awaitingPurchaseOrder' => $awaitingPurchaseOrder,
      'awaitingSalesOrder' => $awaitingSalesOrder
    ]);
  }
}
