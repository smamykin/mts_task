<?php declare(strict_types=1);

namespace App\Tests\functional;

use App\DataFixtures\VehicleFixture;
use App\Entity\Vehicle;
use App\ValueObject\ActionType;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleApiTest extends WebTestCase
{
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

    /**
     * @param string $type
     * @dataProvider providerActionType
     */
    public function testGetPermissionTrue(string $type): void
    {
        $vehicle = $this->getVehicleFixtureForPermissionTest($type, true);

        $this->client->request('GET', "/permission/{$type}/{$vehicle->getNumber()}");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame($this->client->getResponse()->getContent(), json_encode(
            [
                'payload' => [
                    'type' => $type,
                    'permission' => true,
                ],
            ]
        ));
    }

    /**
     * @param string $type
     * @dataProvider providerActionType
     */
    public function testGetPermissionFalse(string $type): void
    {
        $vehicle = $this->getVehicleFixtureForPermissionTest($type, false);

        $this->client->request('GET', "/permission/{$type}/{$vehicle->getNumber()}");

        $this->assertResponseStatusCodeSame(204);
        $this->assertSame($this->client->getResponse()->getContent(), json_encode([
            'payload' => [
                'type' => $type,
                'permission' => false,
            ],
        ]));
    }

    /**
     * @param string $type
     * @dataProvider providerActionType
     */
    public function testAction(string $type): void
    {
        $vehicle = $this->getVehicleFixtureForAction($type);

        $this->client->request('GET', "/action/{$type}/{$vehicle->getNumber()}");

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame($this->client->getResponse()->getContent(), json_encode([
            'payload' => [
                'type' => $type,
                'result' => true,
                'errors' => [],
            ],
        ]));
    }

    public function providerActionType(): array
    {
        return [['in'], ['out'],];
    }

    /**
     * @param $type
     * @param $hasPermission
     * @return Vehicle
     */
    private function getVehicleFixtureForPermissionTest(string $type, bool $hasPermission): Vehicle
    {
        return $this->em
            ->getRepository(Vehicle::class)
            ->findOneByNumber(VehicleFixture::getVehicleNumbersByPermission(new ActionType($type), $hasPermission));
    }

    private function getVehicleFixtureForAction(string $type): Vehicle
    {
        return $this->getVehicleFixtureForPermissionTest($type, true);
    }
}
