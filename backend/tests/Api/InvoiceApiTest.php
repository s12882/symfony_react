<?php

namespace App\Tests\Api;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class InvoiceApiTest extends WebTestCase
{
    private function createInvoice(
        EntityManagerInterface $entityManager,
        ?string                $number = null,
        string                 $supplierName = 'Acme Corp',
        string                 $supplierTaxId = '1234567890',
        string                 $netAmount = '100.00',
        string                 $vatAmount = '20.00',
        string                 $grossAmount = '120.00',
        string                 $currency = 'UAH',
        ?\DateTimeImmutable    $issueDate = null,
        ?\DateTimeImmutable    $dueDate = null,
        InvoiceStatus          $status = InvoiceStatus::Pending,
    ): Invoice
    {
        $invoice = new Invoice(
            number: $number ?? 'INV-' . Uuid::v4(),
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

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'number' => 'INV-' . Uuid::v4(),
            'supplier_name' => 'Acme Ltd',
            'supplier_tax_id' => '1234567890',
            'net_amount' => '100.00',
            'vat_amount' => '20.00',
            'gross_amount' => '120.00',
            'currency' => 'UAH',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-15',
        ], $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'net_amount' => '150.00',
            'vat_amount' => '30.00',
            'due_date' => (new \DateTimeImmutable('+30 days'))->format('Y-m-d'),
        ], $overrides);
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

        $client->request('GET', '/api/invoices/' . Uuid::v4());

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturnsTheInvoice(): void
    {
        $client = self::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $invoice = $this->createInvoice($entityManager);

        $client->request('GET', '/api/invoices/' . $invoice->getId());

        self::assertResponseIsSuccessful();
    }

    public function testStoreCreatesInvoiceAsPending(): void
    {
        $payload = [
            'number' => 'INV-0001',
            'supplier_name' => 'Acme Ltd',
            'supplier_tax_id' => '1234567890',
            'net_amount' => '100.00',
            'vat_amount' => '20.00',
            'gross_amount' => '120.00',
            'currency' => 'UAH',
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-15',
        ];

        $client = self::createClient();

        $client->jsonRequest('POST', '/api/invoices', $payload);

        self::assertResponseStatusCodeSame(201);

        $invoice = self::getContainer()
            ->get(InvoiceRepository::class)
            ->findByNumber('INV-0001');

        self::assertNotNull($invoice);
        self::assertSame(InvoiceStatus::Pending, $invoice->getStatus());
        self::assertSame('100.00', $invoice->getNetAmount());
        self::assertSame('120.00', $invoice->getGrossAmount());
    }

    public function testStoreRejectsDuplicateNumber(): void
    {
        $client = self::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $invoice = $this->createInvoice($entityManager, 'DUPLICATE INVOICE NUMBER');

        $entityManager->persist($invoice);
        $entityManager->flush();

        $client->jsonRequest('POST', '/api/invoices', $this->validPayload(['number' => 'DUPLICATE INVOICE NUMBER']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testStoreRejectsNonPositiveNetAmount(): void
    {
        $client = self::createClient();
        $client->jsonRequest('POST', '/api/invoices', $this->validPayload(['net_amount' => '00.00']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testStoreRejectsNegativeVatAmount(): void
    {
        $client = self::createClient();
        $client->jsonRequest('POST', '/api/invoices', $this->validPayload(['vat_amount' => '-1.00']));

        self::assertResponseStatusCodeSame(422);
    }

    public function testStoreRejectsGrossAmountMismatch(): void
    {
        $client = self::createClient();
        $client->jsonRequest('POST', '/api/invoices', $this->validPayload([
            'net_amount' => '100.00',
            'vat_amount' => '20.00',
            'gross_amount' => '999.00'
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testStoreRejectsDueDateBeforeIssueDate(): void
    {
        $client = self::createClient();
        $client->jsonRequest('POST', '/api/invoices', $this->validPayload([
            'issue_date' => '2026-01-10',
            'due_date' => '2026-01-01',
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateReturns404ForUnknownInvoice(): void
    {
        $client = self::createClient();

        $client->jsonRequest('PUT', '/api/invoices/'.Uuid::v4(), $this->validUpdatePayload());

        self::assertResponseStatusCodeSame(404);
    }

    public function testUpdateRecalculatesGrossAmountAndPersistsChanges(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(self::getContainer()->get(EntityManagerInterface::class));

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload([
            'net_amount' => '150.00',
            'vat_amount' => '30.00',
        ]));

        self::assertResponseIsSuccessful();

        $content = json_decode($client->getResponse()->getContent(), true);
        self::assertSame('180.00', $content['gross_amount']);

        $updated = self::getContainer()->get(InvoiceRepository::class)->find($invoice->getId());
        self::assertSame('150.00', $updated->getNetAmount());
        self::assertSame('30.00', $updated->getVatAmount());
        self::assertSame('180.00', $updated->getGrossAmount());
    }

    public function testUpdateIgnoresClientSuppliedGrossAmount(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(self::getContainer()->get(EntityManagerInterface::class));

        $payload = $this->validUpdatePayload(['net_amount' => '150.00', 'vat_amount' => '30.00']);
        $payload['gross_amount'] = '999999.00'; // not a field on UpdateInvoiceRequest at all

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $payload);

        self::assertResponseIsSuccessful();

        $updated = self::getContainer()->get(InvoiceRepository::class)->find($invoice->getId());
        self::assertSame('180.00', $updated->getGrossAmount());
    }

    public function testUpdateRejectsWhenInvoiceIsNotPending(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(
            self::getContainer()->get(EntityManagerInterface::class),
            status: InvoiceStatus::Approved,
        );

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload());

        self::assertResponseStatusCodeSame(409);
    }

    public function testUpdateRejectsDueDateBeforeIssueDate(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(
            self::getContainer()->get(EntityManagerInterface::class),
            issueDate: new \DateTimeImmutable('2026-01-10'),
        );

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload([
            'due_date' => '2026-01-01',
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateRejectsNonPositiveNetAmount(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(self::getContainer()->get(EntityManagerInterface::class));

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload([
            'net_amount' => '0.00',
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateRejectsNegativeVatAmount(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(self::getContainer()->get(EntityManagerInterface::class));

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload([
            'vat_amount' => '-5.00',
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateDoesNotChangeImmutableFields(): void
    {
        $client = self::createClient();
        $invoice = $this->createInvoice(self::getContainer()->get(EntityManagerInterface::class));
        $originalNumber = $invoice->getNumber();
        $originalSupplierName = $invoice->getSupplierName();
        $originalCurrency = $invoice->getCurrency();
        $originalIssueDate = $invoice->getIssueDate();

        $client->jsonRequest('PUT', '/api/invoices/'.$invoice->getId(), $this->validUpdatePayload());

        self::assertResponseIsSuccessful();

        $updated = self::getContainer()->get(InvoiceRepository::class)->find($invoice->getId());
        self::assertSame($originalNumber, $updated->getNumber());
        self::assertSame($originalSupplierName, $updated->getSupplierName());
        self::assertSame($originalCurrency, $updated->getCurrency());
        self::assertEquals($originalIssueDate, $updated->getIssueDate());
    }
}
