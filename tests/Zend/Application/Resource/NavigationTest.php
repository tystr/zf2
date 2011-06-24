<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZendTest\Application\Resource;

use Zend\Loader\Autoloader,
    Zend\Application,
    Zend\Application\Resource\Navigation as NavigationResource,
    Zend\Registry,
    Zend\Navigation\Page as NavigationPage;

/**
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Application
 */
class NavigationTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->application = new Application\Application('testing');

        $this->bootstrap = new Application\Bootstrap($this->application);

        Registry::_unsetInstance();
    }

    public function tearDown()
    {
    }

    public function testInitializationInitializesNavigationObject()
    {
        $this->markTestSkipped('NavigationResource has fatal error - skip this test now.');
        return;
        $this->bootstrap->registerPluginResource('view');
        $resource = new NavigationResource(array());
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
        $this->assertTrue($resource->getContainer() instanceof \Zend\Navigation\Container);
        $this->bootstrap->unregisterPluginResource('view');
    }

    public function testInitializationReturnsNavigationObject()
    {
        $this->markTestSkipped('NavigationResource has fatal error - skip this test now.');
        return;
        $this->bootstrap->registerPluginResource('view');
        $resource = new NavigationResource(array());
        $resource->setBootstrap($this->bootstrap);
        $test = $resource->init();
        $this->assertTrue($test instanceof \Zend\Navigation\Navigation);
        $this->bootstrap->unregisterPluginResource('view');
    }

    public function testContainerIsStoredInViewhelper()
    {
        $this->markTestSkipped('NavigationResource has fatal error - skip this test now.');
        return;

           $options = array('pages'=> array(new NavigationPage\Mvc(array(
            'action'     => 'index',
            'controller' => 'index'))));

        $this->bootstrap->registerPluginResource('view');
        $resource = new NavigationResource($options);
        $resource->setBootstrap($this->bootstrap)->init();

        $view = $this->bootstrap->getBroker()->getPlugin('view')->getView();
        $number = $view->getHelper('navigation')->getContainer()->count();

        $this->assertEquals($number,1);
        $this->bootstrap->unregisterPluginResource('view');
    }

    public function testContainerIsStoredInRegistry()
    {

           $options = array('pages'=> array(new NavigationPage\Mvc(array(
            'action'     => 'index',
            'controller' => 'index'))), 'storage' => array('registry' => true));

        $resource = new NavigationResource($options);
        $resource->setBootstrap($this->bootstrap)->init();

        $key = NavigationResource::DEFAULT_REGISTRY_KEY;
        $this->assertEquals(Registry::isRegistered($key),true);
        $container = Registry::get($key);
        $number = $container->count();

        $this->assertEquals($number,1);
    }

    /**
     * @group ZF-6747
     */
    public function testViewMethodIsUsedWhenAvailableInsteadOfResourcePlugin()
    {
        $this->markTestSkipped('NavigationResource has fatal error - skip this test now.');
        return;

        $bootstrap = new TestAsset\ZfAppBootstrapCustomView($this->application);
        $bootstrap->registerPluginResource('view');
        $view = $bootstrap->bootstrap('view')->view;

        $this->assertEquals($view->setInMethodByTest,true);
    }

    /**
     * @group ZF-7461
     */
    public function testRegistryIsUsedWhenNumericRegistryValueIsGiven()
    {
        $this->markTestSkipped('NavigationResource has fatal error - skip this test now.');
        return;

        // Register view for cases where registry should/is not (be) used
        $this->bootstrap->registerPluginResource('view');
        $this->bootstrap->getBroker()->getPlugin('view')->getView();

        $options1 = array(
            'pages'=> array(new NavigationPage\Mvc(array(
                'action'     => 'index',
                'controller' => 'index'))
            ),
            'storage' => array('registry' => true)
        );
        $options = array(
            $options1,
            array_merge($options1, array('storage' => array('registry' => '1'))), // Original culprit here
            array_merge($options1, array('storage' => array('registry' => 1))),
            array_merge($options1, array('storage' => array('registry' => false)))
        );

        $results = array();
        $key = NavigationResource::DEFAULT_REGISTRY_KEY;
        foreach($options as $option) {
            $resource = new NavigationResource($option);
            $resource->setBootstrap($this->bootstrap)->init();
            $results[] = Registry::get($key) instanceof \Zend\Navigation\Navigation;
            Registry::set($key,null);
        }

        $this->assertEquals(array(true,true,true,false),$results);
        $this->bootstrap->unregisterPluginResource('view');
    }
}
