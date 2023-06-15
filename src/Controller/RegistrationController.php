<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $doctrine;
    private $passwordHasher;
    private const DAYS_BEFORE_REJECTED_REMOVAL = 1;
    public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        $this->passwordHasher = $passwordHasher;
        $this->doctrine = $doctrine;
    }
    #[Route('/registration', name: 'app_registration')]
    public function index(): Response
    {
         $entityManager = $this->doctrine->getManager();
         $user = new User();
         $user->setEmail("visitor@admin.com");
         $plaintextPassword ="12345";
         $hashedPassword = $this->passwordHasher->hashPassword(
             $user,
             $plaintextPassword
         );
         $user->setPassword($hashedPassword);
         $user->setRoles(["ROLE_MODERATOR"]);
         $entityManager->persist($user);
         $entityManager->flush();

         $entityManager = $this->doctrine->getManager();
         $user = new User();
         $user->setEmail("admin@admin.com");
         $plaintextPassword ="12345";
         $hashedPassword = $this->passwordHasher->hashPassword(
             $user,
             $plaintextPassword
         );
         $user->setPassword($hashedPassword);
         $user->setRoles(["ROLE_ADMIN"]);
         $entityManager->persist($user);
         $entityManager->flush();
        return new Response("Successfully registered You now have two user to test admin@admin.com  and visitor@admin.com passwords 12345");
    }
}
