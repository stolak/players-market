<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\News;
use \Datetime;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class NewsApiController extends AbstractController
{
    private $doctrine;
    private $client;
    public function __construct(HttpClientInterface $client, ManagerRegistry $doctrine)
    {
        $this->client = $client;
        $this->doctrine = $doctrine;
    }
    #[Route('/news/api', name: 'app_news_api')]
    public function index(): Response
    {
        $response = $this->client->request(
            'GET',
            'https://newsapi.org/v2/everything?q=tesla&from='.date("Y-m-d").'&sortBy=publishedAt&apiKey=2439fcb8a3b748e881ad29fce96f89c4'
          );
          $news = json_decode($response->getContent())->articles;
          $entityManager = $this->doctrine->getManager();
          foreach($news as $rec){
  
              $repository = $this->doctrine->getRepository(News::class);
              $artice = $repository->findOneBy(['title' => $rec->title]);
              if($artice){
                $date=new DateTime();
                $artice->setDescription($rec->description. "- last updated". $date->format('Y-m-d H:i:s'));
                $artice->setPicture($rec->urlToImage);
                $artice->setDateAdded($rec->publishedAt);
                $artice->setUpdatedAt($date->format('Y-m-d H:i:s'));
                $entityManager->persist($artice);
                $entityManager->flush();
              }else{
                $artice= new News();
                $artice->setTitle($rec->title);
                $artice->setDescription($rec->description);
                $artice->setPicture($rec->urlToImage);
                $date=new DateTime();
                $artice->setDateAdded($rec->publishedAt);
                $artice->setUpdatedAt($date->format('Y-m-d H:i:s'));
                $entityManager->persist($artice);
                $entityManager->flush();
              }
          }
          return new Response('Successfully downloaded');
      }
}
