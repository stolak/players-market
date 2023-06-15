<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

class NewsController extends AbstractController
{
    private $doctrine;
    private $client;
    private const DAYS_BEFORE_REJECTED_REMOVAL = 1;

    
    #[Route('/news', name: 'app_news')]
    public function index(NewsRepository $articleRepository, PaginatorInterface $paginator, Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $articleQuery = $doctrine->getManager()->getRepository(News::class)
                        ->createQueryBuilder('p')
                        ->getQuery();
        $pagination = $paginator->paginate(
            $articleQuery, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('news/index.html.twig', [
            'articles' => $pagination,
            'controller_name'=>'Latest News',
        ]);
    }
    #[Route('/news/delete/{id}', name: 'app_news_delete')]
    public function update(ManagerRegistry $doctrine, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Hello you are trying to access a page without having ROLE_ADMIN');
        $entityManager = $doctrine->getManager();
        $article = $entityManager->getRepository(News::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for id '.$id
            );
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_news');
    }
}
