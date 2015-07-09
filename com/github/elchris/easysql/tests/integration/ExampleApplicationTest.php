<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/23/14
 * Time: 11:34 PM
 */

namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQLContext;
use com\github\elchris\easysql\tests\EasySQLUnitTest;

/**
 * @see ExampleBaseModel for simple EasySQL configuration and bootsrapping.
 */
class WorldModel extends ExampleBaseModel
{
    const FAKE_COUNTRY = "INSERT INTO `Country` (`Code`, `Name`, `Continent`, `Region`, `SurfaceArea`, `IndepYear`, `Population`, `LifeExpectancy`, `GNP`, `GNPOld`, `LocalName`, `GovernmentForm`, `HeadOfState`, `Capital`, `Code2`)
VALUES ('FKE', 'Fakeland', 'Asia', 'South America', 1234.00, 1900, 1232345, 90.0, 12345.00, 12345.00, 'Fakeworld', 'Fakerepublic', 'Eric Cartman', 537, '');";

    /**
     * @param $countryCode
     * @return City[]
     */
    public function getCitiesByCountryCode($countryCode)
    {
        $q = 'SELECT * FROM City WHERE CountryCode = ?;';
        return $this
            ->db()
            ->getAsCollectionOf(
                new City(),
                $q,
                array($countryCode)
            );
    }//getCitiesByCountryCode

    /**
     * @param string $continent
     * @param double $min
     * @param double $max
     * @return Country[]
     */
    public function getCountriesByContinent($continent, $min = 0.00, $max = 100000000.00)
    {
        $q = 'SELECT * FROM Country WHERE Continent = :continent AND GNP BETWEEN :min AND :max';
        $args = array
        (
            'continent' => $continent,
            'min' => $min,
            'max' => $max
        );
        return $this
            ->db()
            ->getAsCollectionOf(
                new Country(), $q, $args
            );
    }//getCountriesByContinent

    /**
     * @param int $id
     * @return City
     */
    public function getCityById($id)
    {
        $q = 'SELECT * FROM City WHERE ID = ?;';
        return $this
            ->db()
            ->getAsCollectionOf(
                new City(),
                $q,
                array($id)
            )[0];
    }//getCityById

    /**
     * @param City $city
     */
    public function insertCity(City $city)
    {
        $this->db()->insertSingleBean($city, 'City');
    }//getCountryByCode

    /**
     * @param City[] $cities
     */
    public function insertCities($cities)
    {
        $this->db()->insertCollectionOfBeans($cities);
    }//insertCity

    public function prepFakeCountry()
    {
        $fakeCountry = $this->getCountryByCode('FKE');
        if (is_null($fakeCountry)) {
            $this->db()->write(self::FAKE_COUNTRY);
        }
    }//insertCities

    /**
     * @param string $code country code, likely FKE
     * @return Country the country matching the code
     */
    public function getCountryByCode($code)
    {
        $q = 'SELECT * FROM Country WHERE Code = ?';
        $countries = $this
            ->db()
            ->getAsCollectionOf(
                new Country(),
                $q,
                array($code)
            );
        if (count($countries) > 0) {
            return $countries[0];
        } else {
            return null;
        }
    }//prepFakeCountry

    /**
     * @param $number
     * @return City[]
     */
    public function getFakeCities($number)
    {
        $cityIndex = time();
        $cities = array();
        for ($counter = 0; $counter < $number; $counter++) {
            $c = new City();
            $c->CountryCode = "FKE";
            $c->District = "FakeDistrict";
            $c->Population = 9999;
            $c->Name = 'FakeCity_' . $cityIndex;
            $c->ID = $cityIndex;
            array_push($cities, $c);
            $cityIndex++;
        }
        return $cities;
    }//getFakeCities

    public function deleteFakeCities()
    {
        $q = 'DELETE FROM City WHERE CountryCode = "FKE";';
        $this->db()->write($q);
    }
}//WorldModel

class ExampleApplicationTest extends EasySQLUnitTest
{
    /**
     *
     * Pretend this class is either one of your controllers
     * or a top-level Service Class.
     *
     * What's critically important is EasySQLContext $ctx:
     *
     * You want a single instance of $ctx to exist for a given
     * "Execution Path", which could be:
     *
     * a) an HTTP Request as federated by an instance of a Controller
     * b) a Service Class invocation
     *
     * This single $ctx instance must be passed to all instances
     * of Models, Repositories or other classes using EasySQL.
     *
     * This will ensure your database connections and prepared statements
     * are managed as optimally as possible.
     *
     */

    /**
     * @var EasySQLContext $ctx
     */
    private $ctx = null;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        $this->ctx = new EasySQLContext();
        parent::__construct($name, $data, $dataName);
    }

    public function testDriverOptions()
    {
        $wm = new WorldModel($this->ctx);
        $options = $wm->getDriverOptions();
        $this->assertArrayHasKey('foo',$options);
        $this->assertEquals('bar',$options['foo']);
    }//testDriverOptions

    public function testGetCountryAndCapital()
    {
        $wm = new WorldModel($this->ctx);

        $france = $wm->getCountryByCode('FRA');
        $this->debug($france->Name);
        $this->odebug($france);

        $capital = $wm->getCityById($france->Capital);
        $this->debug($capital->Name);
        $this->odebug($capital);

        $this->assertEquals('France', $france->Name);
        $this->assertEquals('Paris', $capital->Name);
    }//testGetCountryAndCapital

    public function testGetCitiesByCountryCode()
    {
        $wm = new WorldModel($this->ctx);
        $frenchCities = $wm->getCitiesByCountryCode('FRA');
        $this->assertNotNull($frenchCities);
        $this->assertNotEmpty($frenchCities);
        $cityNames = array();
        foreach ($frenchCities as $city) {
            $this->debug($city->Name . ': ' . $city->Population);
            array_push($cityNames, $city->Name);
        }
        $this->assertTrue(in_array('Paris', $cityNames));
        $this->assertTrue(in_array('Roubaix', $cityNames));
        $this->assertTrue(in_array('Argenteuil', $cityNames));
        $this->assertTrue(in_array('Nancy', $cityNames));
        $this->assertTrue(in_array('Perpignan', $cityNames));
        $this->assertTrue(in_array('Rouen', $cityNames));
        $this->assertTrue(in_array('Mulhouse', $cityNames));
        $this->assertTrue(in_array('Caen', $cityNames));
        $this->assertTrue(in_array('Metz', $cityNames));
        $this->assertTrue(in_array('Villeurbanne', $cityNames));
        $this->assertTrue(in_array('Tours', $cityNames));
        $this->assertTrue(in_array('Limoges', $cityNames));
        $this->assertTrue(in_array('Aix-en-Provence', $cityNames));
        $this->assertTrue(in_array('Amiens', $cityNames));
        $this->assertTrue(in_array('Lille', $cityNames));
    }//testGetCitiesByCountryCode

    public function testInsertSingleCity()
    {
        $wm = new WorldModel($this->ctx);
        $wm->prepFakeCountry();
        $city = $wm->getFakeCities(1)[0];
        $wm->insertCity($city);
        $fakeCities = $wm->getCitiesByCountryCode('FKE');
        $this->assertCount(1, $fakeCities);
        $wm->deleteFakeCities();
    }//testInsertSingleCity

    public function testInsertMultipleCities()
    {
        $wm = new WorldModel($this->ctx);
        $wm->prepFakeCountry();
        $citiesToInsert = $wm->getFakeCities(10000);
        $wm->insertCities($citiesToInsert);
        $fakeCities = $wm->getCitiesByCountryCode('FKE');
        $this->assertCount(10000, $fakeCities);
        $wm->deleteFakeCities();
    }//testInsertMultipleCities
}//ExampleApplicationTest
