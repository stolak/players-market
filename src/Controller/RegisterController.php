<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Teams;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
  private $doctrine;
  private $passwordHasher;
  public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
  {
    $this->passwordHasher = $passwordHasher;
    $this->doctrine = $doctrine;
  }
  #[Route('/register', name: 'app_register')]
  public function index(AuthenticationUtils $authenticationUtils, Request $request, ValidatorInterface $validator): Response
  {
    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user
    $lastUsername = $authenticationUtils->getLastUsername();
    if (isset($_POST['submit'])) {
      $emailExist = $this->doctrine->getManager()->getRepository(User::class)
        ->findByEmail($request->request->get('_email'));
      if ($emailExist) {
        return $this->render('registration/index.html.twig', [
          'last_username' => $lastUsername,
          'error'         => $error,
          'errors'         => '',
          'err'         => $request->request->get('_email') . ' email/username already exist',
        ]);
      }
      $teamExist = $this->doctrine->getManager()->getRepository(Teams::class)
        ->findOneByName($request->request->get('_team'));
      if ($teamExist) {
        return $this->render('registration/index.html.twig', [
          'last_username' => $lastUsername,
          'error'         => $error,
          'errors'         => '',
          'err'         => $request->request->get('_team') . ' team  already exist',
        ]);
      }
      $entityManager = $this->doctrine->getManager();
      $team = new Teams();
      $team->setName($request->request->get('_team'));
      $team->setCountry($request->request->get('_country'));
      $team->setBalance(is_numeric($request->request->get('_balance')) ? $request->request->get('_balance') : 0);
      // check if there is error in all set contraints

      $entityManager->persist($team);
      $errors = $validator->validate($team);

      if (count($errors) > 0) {
        return $this->render('registration/index.html.twig', [
          'last_username' => $lastUsername,
          'error'         => $error,
          'errors'         => $errors,
          'err'         => ''
        ]);
      }
      $entityManager->flush();

      $user = new User();
      $user->setEmail($request->request->get('_email'));
      $user->setName($request->request->get('_team'));
      $plaintextPassword = $request->request->get('_password');
      $hashedPassword = $this->passwordHasher->hashPassword(
        $user,
        $plaintextPassword
      );
      $user->setPassword($hashedPassword);
      $user->setRoles(["ROLE_MODERATOR"]);
      $user->setTeamId($team->getId());
      $entityManager->persist($user);
      $entityManager->flush();

      return $this->redirectToRoute('app_login');
    }
    return $this->render('registration/index.html.twig', [
      'last_username' => $lastUsername,
      'error'         => $error,
      'errors'         => [],
      'err'         => ''
    ]);
  }
  #[Route('/logout', name: 'app_logout')]
  public function logout(): void
  {
  }
}
