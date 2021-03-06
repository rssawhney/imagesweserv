<?php

namespace AndriesLouw\imagesweserv\Test\Manipulators;

use AndriesLouw\imagesweserv\Api\Api;
use AndriesLouw\imagesweserv\Client;
use AndriesLouw\imagesweserv\Manipulators\Gamma;
use AndriesLouw\imagesweserv\Test\ImagesweservTestCase;
use Jcupitt\Vips\Image;
use Mockery\MockInterface;

class GammaTest extends ImagesweservTestCase
{
    /**
     * @var Client|MockInterface
     */
    private $client;

    /**
     * @var Api
     */
    private $api;

    /**
     * @var Gamma
     */
    private $manipulator;

    public function setUp()
    {
        $this->client = $this->getMockery(Client::class);
        $this->api = new Api($this->client, $this->getManipulators());
        $this->manipulator = new Gamma();
    }

    public function testCreateInstance()
    {
        $this->assertInstanceOf(Gamma::class, $this->manipulator);
    }

    /**
     * Value of 2.2 (default)
     */
    public function testGammaDefaultValue()
    {
        $testImage = $this->inputJpgWithGammaHoliness;
        $expectedImage = $this->expectedDir . '/gamma-2.2.jpg';
        // Above q=90, libvips will write 4:4:4, ie. no subsampling of Cr and Cb
        $params = [
            'gam' => 'true',
            'q' => '95',
        ];

        $uri = basename($testImage);

        $this->client->shouldReceive('get')->with($uri)->andReturn($testImage);

        /** @var Image $image */
        $image = $this->api->run($uri, $params);

        $this->assertEquals(258, $image->width);
        $this->assertEquals(222, $image->height);
        $this->assertSimilarImage($expectedImage, $image);
    }

    /**
     * Value of 3
     */
    public function testGammaValueOf3()
    {
        $testImage = $this->inputJpgWithGammaHoliness;
        $expectedImage = $this->expectedDir . '/gamma-3.0.jpg';
        // Above q=90, libvips will write 4:4:4, ie. no subsampling of Cr and Cb
        $params = [
            'gam' => '3',
            'q' => '95',
        ];

        $uri = basename($testImage);

        $this->client->shouldReceive('get')->with($uri)->andReturn($testImage);

        /** @var Image $image */
        $image = $this->api->run($uri, $params);

        $this->assertEquals(258, $image->width);
        $this->assertEquals(222, $image->height);
        $this->assertSimilarImage($expectedImage, $image);
    }

    /**
     * Alpha transparency
     */
    public function testGammaPngTransparent()
    {
        $testImage = $this->inputPngOverlayLayer1;
        $expectedImage = $this->expectedDir . '/gamma-alpha.png';
        // Above q=90, libvips will write 4:4:4, ie. no subsampling of Cr and Cb
        $params = [
            'w' => '320',
            'gam' => 'true',
            'q' => '95',
        ];

        $uri = basename($testImage);

        $this->client->shouldReceive('get')->with($uri)->andReturn($testImage);

        /** @var Image $image */
        $image = $this->api->run($uri, $params);

        $this->assertEquals(320, $image->width);
        $this->assertSimilarImage($expectedImage, $image);
    }

    public function testGetGamma()
    {
        $this->assertSame(1.5, $this->manipulator->setParams(['gam' => '1.5'])->getGamma());
        $this->assertSame(1.5, $this->manipulator->setParams(['gam' => 1.5])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => null])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => 'a'])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => '.1'])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => '3.999'])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => '0.005'])->getGamma());
        $this->assertSame(2.2, $this->manipulator->setParams(['gam' => '-1'])->getGamma());
    }
}
