<?php

namespace App\Controller;

use App\Entity\Catalog;
use App\Form\CatalogType;
use App\Repository\CatalogRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/catalog')]
class CatalogController extends AbstractController
{
    #[Route('/', name: 'app_catalog_index', methods: ['GET'])]
    public function index(CatalogRepository $catalogRepository): Response
    {
        return $this->render('catalog/index.html.twig', [
            'catalogs' => $catalogRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_catalog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CatalogRepository $catalogRepository): Response
    {
        $catalog = new Catalog();
        $form = $this->createForm(CatalogType::class, $catalog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $catalogRepository->save($catalog, true);

            return $this->redirectToRoute('app_catalog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('catalog/new.html.twig', [
            'catalog' => $catalog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_catalog_show', methods: ['GET'])]
    public function show(Catalog $catalog, ProductRepository $productRepository): Response
    {
        $products=$catalog->getProducts();
        return $this->render('catalog/show.html.twig', [
            'catalog' => $catalog,
            'products'=>$products,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_catalog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Catalog $catalog, CatalogRepository $catalogRepository): Response
    {
        $form = $this->createForm(CatalogType::class, $catalog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $catalogRepository->save($catalog, true);

            return $this->redirectToRoute('app_catalog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('catalog/edit.html.twig', [
            'catalog' => $catalog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_catalog_delete', methods: ['POST'])]
    public function delete(Request $request, Catalog $catalog, CatalogRepository $catalogRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$catalog->getId(), $request->request->get('_token'))) {
            $catalogRepository->remove($catalog, true);
        }

        return $this->redirectToRoute('app_catalog_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/api/api', name: 'app_catalog_api')]
    public function api(Request $request, CatalogRepository $catalogRepository): Response
    {
//        $payload = $request->toArray();

        $catalog_products = $catalogRepository->findAll();
        return $this->render("catalog/api.json.twig",[
            'catalogs' =>$catalog_products,
        ]);
    }

    #[Route('/api/whole', name: 'app_catalog_api_whole')]
    public function whole(Request $request, CatalogRepository $catalogRepository): Response
    {
        $catalog_products = $catalogRepository->findAll();
        return $this->render("catalog/api_whole.json.twig",[
            'catalogs' =>$catalog_products,
        ]);
    }

    #[Route('/api/take', name: 'app_catalog_api_take')]
    public function take(Request $request, CatalogRepository $catalogRepository): Response
    {
        $payload = $request->toArray();
        //JSON / POST
        //{
        //    "name": "name_of_product",
        //    "quantity": number
        //}
        $catalog_products=$catalogRepository->findOneBy(['name'=>$payload['name']]);
        $products=$catalog_products->getProducts();
        $productArray=[];
        foreach ($products as $product){
            if ($product->getQuantity()>=$payload['quantity']){
                $productArray[$product->getId()]=[
                    'series' => $product->getSeries(),
                    'exp_date' => $product->getExpDate()->format('d-m-Y'),
                ];
            }
        }
        return $this->render("catalog/api_take.json.twig",[
            'products' =>$productArray,
            'request_prod'=>$payload['name'],
            'request_quant'=>$payload['quantity'],
        ]);
    }

}
