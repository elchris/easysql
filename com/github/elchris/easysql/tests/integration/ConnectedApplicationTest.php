<?php
/**
 * Created with PhpStorm.
 * User: Chris Holland
 * Date: 12/11/14
 * Time: 12:29 PM
 */

namespace com\github\elchris\easysql\tests\integration;

use com\github\elchris\easysql\EasySQLContext;
use com\github\elchris\easysql\tests\EasySQLUnitTest;


class MyApp extends ExampleBaseModel
{
        private $arrayMode = false;//insertLotsOfCities

    public function __construct(EasySQLContext $ctx, $isArrayMode = false)
    {
        $this->arrayMode = $isArrayMode;
        parent::__construct($ctx);
    }//getCitiesByCountryCode

    public function insertLotsOfCities($number, $oneByOne = false)
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
            if ($oneByOne) {
                $this->db()->insertSingleBean($c, 'City');
            } else {
                array_push($cities, $c);
            }
            $cityIndex++;
        }
        if (!$oneByOne) {
            $this->db()->insertCollectionOfBeans($cities, 'City');
        }
    }//getCountriesByContinent

    /**
     * @param $countryCode
     * @return City[]
     */
    public function getCitiesByCountryCode($countryCode)
    {
        $q = 'SELECT * FROM City WHERE CountryCode = ?;';
        if ($this->arrayMode) {
            return $this
                ->db()
                ->getAsArray($q, array($countryCode));
        } else {
            return $this
                ->db()
                ->getAsCollectionOf(
                    new City(),
                    $q,
                    array($countryCode)
                );
        }
    }//getCityById

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

        if ($this->arrayMode) {
            return $this
                ->db()
                ->getAsArray($q, $args);
        } else {
            return $this
                ->db()
                ->getAsCollectionOf(
                    new Country(), $q, $args
                );
        }
    }//getCountryByCode

    /**
     * @param int $id
     * @return City[]
     */
    public function getCityById($id)
    {
        $q = 'SELECT * FROM City WHERE ID = ?;';
        if ($this->arrayMode) {
            return $this
                ->db()
                ->getAsArray($q, array($id));
        } else {
            return $this
                ->db()
                ->getAsCollectionOf(
                    new City(),
                    $q,
                    array($id)
                );
        }
    }//prepFakeCountry

public function prepFakeCountry()
    {
        $fakeCountry = $this->getCountryByCode('FKE');
        if (is_null($fakeCountry)) {
            $this->db()->write(self::FAKE_COUNTRY);
        }
    }

    /**
     * @param string $code country code, likely FKE
     * @return Country the country matching the code
     */
    public function getCountryByCode($code)
    {
        $q = 'SELECT * FROM Country WHERE Code = ?';
        $countries = $this->db()->getAsCollectionOf(new Country(), $q, array($code));
        if (count($countries) > 0) {
            return $countries[0];
        } else {
            return null;
        }
    }//MyApp Constructor
}//MyApp

class ConnectedApplicationTest extends EasySQLUnitTest
{
    public function testGetCitiesForRussia()
    {
        $a = new MyApp(new EasySQLContext());
        $russianCities = $a->getCitiesByCountryCode('RUS');
        foreach ($russianCities as $city) {
            $this->assertEquals('RUS', $city->CountryCode);
            $this->assertGreaterThan(1000, $city->Population);
            $this->assertGreaterThan(1, $city->ID);
        }
    }//testGetCitiesForRussia

    public function testGetCountriesForContinent()
    {
        $a = new MyApp(new EasySQLContext());
        $northAmericanCountries = $a->getCountriesByContinent('North America', 500.0);
        foreach ($northAmericanCountries as $country) {
            $this->assertGreaterThan(500.00, $country->GNP);
        }
        $northAmericanCountries = $a->getCountriesByContinent('North America', 100.0, 100000.0);
        foreach ($northAmericanCountries as $country) {
            $this->assertGreaterThan(100.00, $country->GNP);
            $this->assertLessThan(100000.00, $country->GNP);
            $capital = $a->getCityById($country->Capital)[0];
            $this->assertGreaterThan(1, $capital->Population);
        }
    }//testGetCountriesForContinent

    public function testGetCitiesForRussiaAsArrayMode()
    {
        $a = new MyApp(new EasySQLContext(), true);
        $russianCities = $a->getCitiesByCountryCode('RUS');
        foreach ($russianCities as $city) {
            $this->assertEquals('RUS', $city['CountryCode']);
            $this->assertGreaterThan(1000, $city['Population']);
            $this->assertGreaterThan(1, $city['ID']);
        }
    }//testGetCitiesForRussiaAsArrayMode

    public function testAccuracyOfCityInserts()
    {
        $a = new MyApp(new EasySQLContext(), true);
        $a->prepFakeCountry();

        $totalCities = 1000;

        $start = microtime(true);
        $a->insertLotsOfCities($totalCities, true);
        $this->verifyCityCountAndReset($a, $totalCities);
        $end = microtime(true);
        $timingOne = ($end - $start);
        $this->debug('timing 1: ' . $timingOne);

        $start = microtime(true);
        $a->insertLotsOfCities($totalCities, false);
        $this->verifyCityCountAndReset($a, $totalCities);
        $end = microtime(true);
        $timingTwo = ($end - $start);
        $this->debug('timing 2: ' . $timingTwo);
        $this->debug('ratio ' . $timingOne / $timingTwo);
    }//testAccuracyOfCityInserts

    /**
     * @param MyApp $a
     * @param int $totalCities
     * @param bool $skipVerification
     */
    private function verifyCityCountAndReset(MyApp $a, $totalCities, $skipVerification = false)
    {
        if (!$skipVerification) {
            $cities = $a->getCitiesByCountryCode('FKE');
            $this->assertCount($totalCities, $cities);
        }
        $a->db()->write('DELETE FROM City WHERE CountryCode = "FKE";');
        if (!$skipVerification) {
            $cities = $a->getCitiesByCountryCode('FKE');
            $this->assertCount(0, $cities);
        }
    }//testSpeedOfInserts

    public function testSpeedOfInserts()
    {
        $a = new MyApp(new EasySQLContext(), true);
        $a->prepFakeCountry();

        $totalCities = 10000;

        $start = microtime(true);
        $a->insertLotsOfCities($totalCities, true);
        $this->verifyCityCountAndReset($a, $totalCities, true);
        $end = microtime(true);
        $timingOne = ($end - $start);
        $this->debug('timing 1: ' . $timingOne);

        $start = microtime(true);
        $a->insertLotsOfCities($totalCities, false);
        $this->verifyCityCountAndReset($a, $totalCities, true);
        $end = microtime(true);
        $timingTwo = ($end - $start);
        $this->debug('timing 2: ' . $timingTwo);
        $this->debug('ratio ' . $timingOne / $timingTwo);
    }//testAccuracyOfCityInserts
}//ConnectedApplicationTest
