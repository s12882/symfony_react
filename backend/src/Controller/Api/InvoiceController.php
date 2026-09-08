<?php

namespace App\Controller\Api;

use App\Dto\CreateInvoiceRequest;
use App\Dto\InvoiceResource;
use App\Dto\UpdateInvoiceRequest;
use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use App\Service\InvoiceService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/invoices')]
class InvoiceController
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] ?InvoiceStatus $status = null,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $paginator = $this->invoiceService->list($status, $page);

        return new JsonResponse([
            'data' => array_map(InvoiceResource::fromEntity(...), iterator_to_array($paginator)),
            'meta' => [
                'current_page' => $page,
                'last_page' => (int) ceil(count($paginator) / InvoiceService::PER_PAGE),
                'total' => count($paginator),
            ],
        ]);
    }

    #[Route('/{id}', methods: ['GET'], requirements: ['id' => Requirement::UUID])]
    public function show(Invoice $invoice): JsonResponse
    {
        return new JsonResponse(InvoiceResource::fromEntity($invoice));
    }

    #[Route('', methods: ['POST'])]
    public function store(#[MapRequestPayload] CreateInvoiceRequest $dto): JsonResponse
    {
        $invoice = $this->invoiceService->create($dto);

        return new JsonResponse(InvoiceResource::fromEntity($invoice), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'], requirements: ['id' => Requirement::UUID])]
    public function update(Invoice $invoice, #[MapRequestPayload] UpdateInvoiceRequest $dto): JsonResponse
    {
        $invoice = $this->invoiceService->update($invoice, $dto);

        return new JsonResponse(InvoiceResource::fromEntity($invoice));
    }
}
