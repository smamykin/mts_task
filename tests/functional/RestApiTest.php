<?php declare(strict_types=1);

namespace App\Tests\functional;

use App\DataFixtures\VehicleFixture;
use App\Entity\Vehicle;
use App\Entity\Visit;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Generator;
use OutOfRangeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Serializer;

class RestApiTest extends WebTestCase
{
    const JSON_DATE_FORMAT = 'Y-m-d H:i:s';
    const GET_VEHICLE_SET_COUNT = 4;
    /**
     * @var KernelBrowser
     */
    private $client;
    /**
     * @var ObjectManager
     */
    private $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    public function testGetVehicles(): void
    {
        //test
        $this->client->request('GET', "/vehicles");

        //check
        $this->assertResponseStatusCodeSame(200);

        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $this->getAllVehicles()],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @param string $vehicleNumber
     * @dataProvider providerVehicleNumbers
     */
    public function testGetVehicle(string $vehicleNumber): void
    {
        $vehicle = $this->em->getRepository(Vehicle::class)->findOneByNumber($vehicleNumber);
        $this->client->request('GET', "/vehicle/{$vehicle->getNumber()}");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $vehicle],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );
    }

    public function testGetVehicleVisits(): void
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $this->client->request('GET', "/vehicle/{$vehicle->getNumber()}/visits");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $this->getAllVisitsByVehicle($vehicle)],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPostVehicleVisit(): Visit
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $this->client->request('GET', "/vehicle/{$vehicle->getNumber()}/visits");

        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $newId = $content['payload']['id'];
        $this->assertNotEmpty($newId);

        $newVisit = $this->getVisitById($newId);

        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $newVisit],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );

        return $newVisit;
    }

    /**
     * @depends testPostVehicleVisit
     * @param Visit $visit
     */
    public function testPatchVehicleVisit(Visit $visit): void
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $now = new DateTimeImmutable();
        $this->client->request(
            'POST',
            "/vehicle/{$vehicle->getNumber()}/visit/{$visit->getId()}",
            ['closed_at' => $now->format(self::JSON_DATE_FORMAT)]
        );

        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $newId = $content['payload']['id'];
        $this->assertNotEmpty($newId);

        $newVisit = $this->getVisitById($newId);

        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $newVisit],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );

        $this->assertSame($now, $newVisit->getClosedAt());
    }

    public function testGetLastVisit()
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $this->client->request('GET', "/vehicle/{$vehicle->getNumber()}/last-visit");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $this->getLastVisitOfVehicle($vehicle)],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @return Generator
     */
    public function providerVehicleNumbers(): Generator
    {
        $vehicleNumbers = VehicleFixture::getVehicleNumbers();
        if (count($vehicleNumbers) < self::GET_VEHICLE_SET_COUNT) {
            throw new OutOfRangeException('Для теста не достаточно фикстур.');
        }
        yield array_splice($vehicleNumbers, array_rand($vehicleNumbers),1);
        yield array_splice($vehicleNumbers, array_rand($vehicleNumbers),1);
        yield array_splice($vehicleNumbers, array_rand($vehicleNumbers),1);
        yield array_splice($vehicleNumbers, array_rand($vehicleNumbers),1);
    }

    /**
     * @return Serializer
     */
    private function getSerializer(): Serializer
    {
        return $this->client->getContainer()->get('serializer');
    }

    /**
     * @return Vehicle[]
     */
    private function getAllVehicles(): array
    {
        return $this->em->getRepository(Vehicle::class)->findAll();
    }

    /**
     * @param Vehicle $vehicle
     * @return Visit[]
     */
    private function getAllVisitsByVehicle(Vehicle $vehicle): array
    {
        return $this->em
            ->getRepository(Visit::class)
            ->findAllByVehicle($vehicle);
    }

    /**
     * @return Vehicle
     */
    private function getVehicleForVisitsTests(): Vehicle
    {
        $vehicleNumber = VehicleFixture::getFixtureIdForGetVehicleVisits();

        return $this->em->getRepository(Vehicle::class)->findOneByNumber($vehicleNumber);
    }

    private function getLastVisitOfVehicle($vehicle)
    {
        return $this->em
            ->getRepository(Visit::class)
            ->findLastOfVehicle($vehicle);
    }

    /**
     * @param $newId
     * @return mixed
     */
    private function getVisitById($newId)
    {
        return $this->em->getRepository(Visit::class)->find($newId);
    }
}
