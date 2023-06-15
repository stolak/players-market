<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Players;
use App\Entity\Teams;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlayersController extends AbstractController
{
  private $doctrine;
  private $paginator;
  private $rootDir;
  public function __construct(ManagerRegistry $doctrine, PaginatorInterface $paginator, KernelInterface $kernel)
  {
    $this->doctrine = $doctrine;
    $this->paginator = $paginator;
    $this->rootDir = $kernel->getProjectDir();
  }


  #[Route('/new-player', name: 'app_register_palyer')]
  public function index(Request $request, ValidatorInterface $validator): Response
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    if (isset($_POST['submit'])) {

      $file = $request->files->get('photo');
      if ($file) {
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($this->rootDir . '/public/assets/images/', $fileName);
        $photo = '/assets/images/' . $fileName;
      } else {
        $photo = '/assets/images/6188201820e198d568bbf3a46eaf0429.jpg';
      }
      $entityManager = $this->doctrine->getManager();
      $player = new Players();
      $player->setFirstName($request->request->get('_firstName'));
      $player->setLastName($request->request->get('_lastName'));
      $player->setTeamId($this->getUser()->getTeamId());
      $player->setMarketValue(is_numeric($request->request->get('_marketValue')) ? $request->request->get('_marketValue') : 0);
      $player->setPhoto($photo);
      $entityManager->persist($player);
      $errors = $validator->validate($player);

      if (count($errors) > 0) {
        $playerQuery = $this->doctrine->getManager()->getRepository(Players::class)
          ->findByTeam($this->getUser()->getTeamId());
        $pagination = $this->paginator->paginate(
          $playerQuery, /* query NOT result */
          $request->query->getInt('page', 1), /*page number*/
          2 /*limit per page*/
        );
        return $this->render('players/index.html.twig', [
          'error'         => '',
          'players' => $pagination,
          'errors'         => $errors,
        ]);
      }
      $entityManager->flush();
    }

    $playerQuery = $this->doctrine->getManager()->getRepository(Players::class)
      ->findByTeam($this->getUser()->getTeamId());

    $pagination = $this->paginator->paginate(
      $playerQuery, /* query NOT result */
      $request->query->getInt('page', 1), /*page number*/
      2 /*limit per page*/
    );
    // dd($pagination);
    return $this->render('players/index.html.twig', [
      'error'         => '',
      'players' => $pagination,
      'errors'         => [],
    ]);
  }
}
