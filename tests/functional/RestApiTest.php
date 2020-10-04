<?php declare(strict_types=1);

namespace App\Tests\functional;

use App\DataFixtures\VehicleFixture;
use App\Entity\Vehicle;
use App\Entity\Visit;
use DateTimeImmutable;
use DateTimeInterface;
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
        $this->client->request('GET', "/api/v0.1/vehicles");

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
        $this->client->request('GET', "/api/v0.1/vehicle/{$vehicle->getNumber()}");

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
        $this->client->request('GET', "/api/v0.1/vehicle/{$vehicle->getNumber()}/visits");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            $this->getSerializer()->serialize(
                ['payload' => $vehicle->getVisits()],
                'json'
            ),
            $this->client->getResponse()->getContent()
        );
    }

    public function testPostVehicleVisit(): Visit
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $this->client->request('POST', "/api/v0.1/vehicle/{$vehicle->getNumber()}/visits",['payload' =>[]]);

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
     */
    public function testPatchVehicleVisit(): void
    {
        $visit = $this->testPostVehicleVisit(); // чтобы появилась запись которую можно отредактировать
        if (empty($visit)) {
            $this->fail('не удалось создать запись для редактирования.');
        }

        $vehicle = $this->getVehicleForVisitsTests();
        $now = new DateTimeImmutable();
        $this->client->request(
            'POST',
            "/api/v0.1/vehicle/{$vehicle->getNumber()}/visit/{$visit->getId()}",
            ['payload'=> ['closed_at' => $now->format(self::JSON_DATE_FORMAT)]]
        );

        $this->assertResponseStatusCodeSame(200);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $newId = $content['payload']['id'];
        $this->assertNotEmpty($newId);

        $newVisit = $this->getVisitById($newId);

        $this->assertSame(
            $this->getSerializer()->serialize(['payload' => $newVisit], 'json'),
            $this->client->getResponse()->getContent()
        );

        $this->assertInstanceOf(DateTimeInterface::class, $newVisit->getClosedAt());
        $this->assertSame(
            $now->format(self::JSON_DATE_FORMAT),
            $newVisit->getClosedAt()->format(self::JSON_DATE_FORMAT)
        );
    }

    public function testGetLastVisit()
    {
        $vehicle = $this->getVehicleForVisitsTests();
        $this->client->request('GET', "/api/v0.1/vehicle/{$vehicle->getNumber()}/last-visit");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            $this->getSerializer()->serialize(['payload' => $vehicle->getVisits()->last()], 'json'),
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
     * @return Vehicle
     */
    private function getVehicleForVisitsTests(): Vehicle
    {
        $vehicleNumber = VehicleFixture::getFixtureIdForGetVehicleVisits();

        return $this->em->getRepository(Vehicle::class)->findOneByNumber($vehicleNumber);
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
