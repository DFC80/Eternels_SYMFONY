<?php

namespace App\Controller;

use App\Entity\MarketListing;
use App\Form\MarketListingType;
use App\Repository\MarketListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/brocante')]
#[IsGranted('ROLE_USER')]
class MarketController extends AbstractController
{
    #[Route('/', name: 'app_market_index')]
    public function index(MarketListingRepository $repo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $all = $repo->findVisibleForUser($user);

        $mine  = array_values(array_filter($all, fn($l) => $l->getSeller() === $user));
        $others = array_values(array_filter($all, fn($l) => $l->getSeller() !== $user));

        $form = $this->createForm(MarketListingType::class, new MarketListing(), [
            'action' => $this->generateUrl('app_market_new'),
        ]);

        return $this->render('market/index.html.twig', [
            'my_listings'    => $mine,
            'other_listings' => $others,
            'form'           => $form,
            'total_active'   => $repo->countActive(),
        ]);
    }

    #[Route('/new', name: 'app_market_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $listing = new MarketListing();
        $form = $this->createForm(MarketListingType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $listing->setSeller($user);

            $this->handlePhotoUploads($form, $listing);

            $em->persist($listing);
            $em->flush();

            $this->addFlash('success', 'Votre annonce a été publiée avec succès !');

            $from = $request->request->get('_from', '');
            return $this->redirectToRoute($from === 'home' ? 'app_home' : 'app_market_index');
        }

        return $this->render('market/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'app_market_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(MarketListing $listing): Response
    {
        return $this->render('market/show.html.twig', ['listing' => $listing]);
    }

    #[Route('/{id}/edit', name: 'app_market_edit', methods: ['GET', 'POST'])]
    public function edit(MarketListing $listing, Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$listing->isOwnedBy($user) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(MarketListingType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePhotoUploads($form, $listing, append: true);
            $listing->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Annonce mise à jour avec succès.');
            return $this->redirectToRoute('app_market_index');
        }

        return $this->render('market/edit.html.twig', [
            'listing' => $listing,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'app_market_status', methods: ['POST'])]
    public function changeStatus(
        MarketListing $listing,
        string $status,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$listing->isOwnedBy($user) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!in_array($status, ['ACTIVE', 'VENDU', 'CLOS'], true)) {
            throw $this->createNotFoundException('Statut invalide.');
        }

        if (!$this->isCsrfTokenValid('market_status_' . $listing->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_market_index');
        }

        $listing->setStatus($status);
        $listing->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $labels = ['ACTIVE' => 'réactivée', 'VENDU' => 'marquée comme vendue', 'CLOS' => 'clôturée'];
        $this->addFlash('success', 'Annonce ' . $labels[$status] . '.');
        return $this->redirectToRoute('app_market_index');
    }

    #[Route('/{id}/delete', name: 'app_market_delete', methods: ['POST'])]
    public function delete(MarketListing $listing, Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$listing->isOwnedBy($user) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_market_' . $listing->getId(), $request->request->get('_token'))) {
            $this->deletePhotos($listing);
            $em->remove($listing);
            $em->flush();
            $this->addFlash('success', 'Annonce supprimée.');
        }

        return $this->redirectToRoute('app_market_index');
    }

    #[Route('/{id}/photo/{idx}/delete', name: 'app_market_photo_delete', methods: ['POST'])]
    public function deletePhoto(MarketListing $listing, int $idx, Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$listing->isOwnedBy($user) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('del_photo_' . $listing->getId() . '_' . $idx, $request->request->get('_token'))) {
            $photos = $listing->getPhotos() ?? [];
            if (isset($photos[$idx])) {
                $path = $this->getParameter('kernel.project_dir') . '/public' . $photos[$idx];
                if (is_file($path)) {
                    unlink($path);
                }
                array_splice($photos, $idx, 1);
                $listing->setPhotos(empty($photos) ? null : array_values($photos));
                $listing->setUpdatedAt(new \DateTimeImmutable());
                $em->flush();
            }
        }

        return $this->redirectToRoute('app_market_edit', ['id' => $listing->getId()]);
    }

    private function handlePhotoUploads(\Symfony\Component\Form\FormInterface $form, MarketListing $listing, bool $append = false): void
    {
        $files = $form->get('photoFiles')->getData();
        if (!$files) {
            return;
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/market';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $photos = $append ? ($listing->getPhotos() ?? []) : [];
        foreach ($files as $file) {
            $ext = $file->guessExtension() ?? 'jpg';
            $name = uniqid('ml_', true) . '.' . $ext;
            $file->move($uploadDir, $name);
            $photos[] = '/uploads/market/' . $name;
        }
        $listing->setPhotos(empty($photos) ? null : $photos);
    }

    private function deletePhotos(MarketListing $listing): void
    {
        $publicDir = $this->getParameter('kernel.project_dir') . '/public';
        foreach ($listing->getPhotos() ?? [] as $photo) {
            $path = $publicDir . $photo;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
