<?php declare(strict_types=1);

namespace App\Tests\functional;

use App\Entity\Vehicle;
use App\Entity\Visit;
use DateTimeInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VisitTest extends WebTestCase
{
    /**
     * @var ObjectManager
     */
    private $em;

    protected function setUp()
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testCreateNewEntity(): void
    {
        $obj = new Visit();
        $vehicle = $this->createVehicle();
        $obj->setVehicle($vehicle);

        $this->em->persist($obj);
        $this->em->flush();
        $this->em->clear();
        $result = $this->em->getRepository(Visit::class)->find($obj->getId());
        $this->assertInstanceOf(DateTimeInterface::class, $result->getCreatedAt());
    }

    /**
     * @return Vehicle
     */
    private function createVehicle(): Vehicle
    {
        $vehicle = new Vehicle();
        $vehicle->setNumber(uniqid());
        $vehicle->setIsActive(true);

        return $vehicle;
    }
}
