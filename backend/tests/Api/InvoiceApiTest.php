<?php

namespace App\Tests\Api;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class InvoiceApiTest extends WebTestCase
{
    private function createInvoice(
        EntityManagerInterface $entityManager,
        ?string $number = null,
        string $supplierName = 'Acme Corp',
        string $supplierTaxId = '1234567890',
        string $netAmount = '100.00',
        string $vatAmount = '20.00',
        string $grossAmount = '120.00',
        string $currency = 'UAH',
        ?\DateTimeImmutable $issueDate = null,
        ?\DateTimeImmutable $dueDate = null,
        InvoiceStatus $status = InvoiceStatus::Pending,
    ): Invoice {
        $invoice = new Invoice(
            number: $number ?? 'INV-'.Uuid::v4(),
            supplierName: $supplierName,
            supplierTaxId: $supplierTaxId,
            netAmount: $netAmount,
            vatAmount: $vatAmount,
            grossAmount: $grossAmount,
            currency: $currency,
            issueDate: $issueDate ?? new \DateTimeImmutable('-10 days'),
            dueDate: $dueDate ?? new \DateTimeImmutable('+10 days'),
            status: $status,
        );

        $entityManager->persist($invoice);
        $entityManager->flush();

        return $invoice;
    }

    public function testListInvoicesReturnsSuccessfulResponse(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/invoices');

        self::assertResponseIsSuccessful();
    }

    public function testShowReturns404ForUnknownInvoice(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/invoices/'.Uuid::v4());

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturnsTheInvoice(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $invoice = $this->createInvoice($entityManager);

        $entityManager->persist($invoice);
        $entityManager->flush();

        $client->request('GET', '/api/invoices/'.$invoice->getId());

        self::assertResponseIsSuccessful();
    }
}
