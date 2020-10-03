<?php declare(strict_types=1);

namespace App\Tests\functional;

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

    /**
     * @param $newId
     * @return mixed
     */
    public function getVisitById($newId)
    {
        return $this->em->getRepository(Visit::class)->find($newId);
    }

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
     * @param int $vehicleId
     * @dataProvider providerVehicleId
     */
    public function testGetVehicle(int $vehicleId): void
    {
        $vehicle = $this->em->getRepository(Vehicle::class)->find($vehicleId);
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
     * @depends \App\Tests\functional\RestApiTest::testPostVehicleVisit()
     * @param Visit $visit
     * @return Visit
     */
    public function testPatchVehicleVisit(Visit $visit): Visit
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

        return $visit;
    }

    public function testGetLastVisit()
    {
        $vehicle = $this->getVehicleForLastVisitsTests();
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
    public function providerVehicleId(): Generator
    {
        $vehicleIds = VehicleFixture::getVehicleIds();
        if (count($vehicleIds) < self::GET_VEHICLE_SET_COUNT) {
            throw new OutOfRangeException('Для теста не достаточно фикстур.');
        }
        yield array_splice($vehicleIds, array_rand($vehicleIds),1)[0];
        yield array_splice($vehicleIds, array_rand($vehicleIds),1)[0];
        yield array_splice($vehicleIds, array_rand($vehicleIds),1)[0];
        yield array_splice($vehicleIds, array_rand($vehicleIds),1)[0];
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
     * @return array
     */
    private function getVehicleForVisitsTests(): array
    {
        $vehicleId = VehicleFixture::getFirxtureIdForGetVehicleVisits();

        return $this->em->getRepository(Vehicle::class)->find($vehicleId);
    }

    private function getLastVisitOfVehicle($vehicle)
    {
        return $this->em
            ->getRepository(Visit::class)
            ->findLastOfVehicle($vehicle);
    }

    private function getVehicleForLastVisitsTests()
    {
        $vehicleId = VehicleFixture::getFirxtureIdForGetVehicleLastVisit();

        return $this->em->getRepository(Vehicle::class)->find($vehicleId);
    }
}
