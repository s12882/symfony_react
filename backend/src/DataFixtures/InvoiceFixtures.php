<?php

namespace App\DataFixtures;

use App\Entity\Invoice;
use App\Enum\InvoiceStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class InvoiceFixtures extends Fixture
{
    private const CURRENCIES = ['UAH', 'USD', 'EUR'];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $this->createInvoice($manager, $faker, InvoiceStatus::Pending);
        $this->createInvoice($manager, $faker, InvoiceStatus::Approved);
        $this->createInvoice($manager, $faker, InvoiceStatus::Rejected);

        for ($i = 0; $i < 12; $i++) {
            $this->createInvoice($manager, $faker, InvoiceStatus::Pending);
        }

        $manager->flush();
    }

    private function createInvoice(ObjectManager $manager, Generator $faker, InvoiceStatus $status): void
    {
        $netAmount = number_format($faker->randomFloat(2, 100, 10000), 2, '.', '');
        $vatAmount = number_format((float) $netAmount * 0.2, 2, '.', '');
        $grossAmount = bcadd($netAmount, $vatAmount, 2);

        $issueDate = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-60 days', '-1 days'));
        $dueDate = $issueDate->modify('+' . $faker->numberBetween(7, 30) . ' days');

        $invoice = new Invoice(
            number: strtoupper($faker->unique()->bothify('INV-####-???')),
            supplierName: $faker->company(),
            supplierTaxId: $faker->numerify('##########'),
            netAmount: $netAmount,
            vatAmount: $vatAmount,
            grossAmount: $grossAmount,
            currency: $faker->randomElement(self::CURRENCIES),
            issueDate: $issueDate,
            dueDate: $dueDate,
            status: $status,
        );

        $manager->persist($invoice);
    }
}
