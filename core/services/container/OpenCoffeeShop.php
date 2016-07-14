<?php
namespace EventEspresso\core\services\container;

use EventEspresso\tests\mocks\core\services\container\Coffee;
use EventEspresso\tests\mocks\core\services\container\HonduranBean;
use EventEspresso\tests\mocks\core\services\container\KenyanBean;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class OpenCoffeeShop
 * Initialize and configure the CoffeeSop DI container
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         $VID:$
 */
class OpenCoffeeShop {

	/**
	 * @var CoffeeShop $CoffeeShop
	 */
	private $CoffeeShop;

	/**
	 * @var DependencyInjector $DependencyInjector
	 */
	private $DependencyInjector;



	/**
	 * OpenCoffeeShop constructor.
	 */
	public function __construct() {
		// instantiate the container
		$this->CoffeeShop = new CoffeeShop();
		// create a dependency injector class for resolving class constructor arguments
		$this->DependencyInjector = new DependencyInjector(
			$this->CoffeeShop,
			new \EEH_Array()
		);
		// and some coffeemakers, one for creating new instances
		$this->CoffeeShop->addCoffeeMaker(
			new NewCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_NEW
		);
		// one for shared services
		$this->CoffeeShop->addCoffeeMaker(
			new SharedCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_SHARED
		);
		// and one for classes that only get loaded
		$this->CoffeeShop->addCoffeeMaker(
			new LoadOnlyCoffeeMaker( $this->CoffeeShop, $this->DependencyInjector ),
			CoffeeMaker::BREW_LOAD_ONLY
		);
		// add default recipe, which should handle loading for most PSR-4 compatible classes
		// as long as they are not type hinting for interfaces
		$this->CoffeeShop->addRecipe(
			new Recipe(
				Recipe::DEFAULT_ID
			)
		);
	}



	/**
	 * @return \EventEspresso\core\services\container\CoffeeShop
	 */
	public function CoffeeShop() {
		return $this->CoffeeShop;
	}



	public function addRecipes() {

		// PSR-4 compatible class with aliases
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'CommandHandlerManager',
				'EventEspresso\core\services\commands\CommandHandlerManager',
                array(),
				CoffeeMaker::BREW_SHARED,
				array(
					'CommandHandlerManagerInterface',
					'EventEspresso\core\services\commands\CommandHandlerManagerInterface',
				)
			)
		);
		// PSR-4 compatible class with aliases, which dependency on CommandHandlerManager
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'CommandBus',
				'EventEspresso\core\services\commands\CommandBus',
                array(),
				CoffeeMaker::BREW_SHARED,
				array(
					'CommandBusInterface',
					'EventEspresso\core\services\commands\CommandBusInterface',
				)
			)
		);
		// LEGACY classes that are NOT compatible with PSR-4 autoloading, and so must specify a filepath
		// add a wildcard recipe for loading legacy core interfaces
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EEI_*',
				'',
                array(),
				CoffeeMaker::BREW_LOAD_ONLY,
				array(),
				array(
					EE_INTERFACES . '*.php',
					EE_INTERFACES . '*.interfaces.php',
				)
			)
		);
		// add a wildcard recipe for loading models
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EEM_*',
                '',
                array(),
				CoffeeMaker::BREW_SHARED,
				array(),
				EE_MODELS . '*.model.php'
			)
		);
		// add a wildcard recipe for loading core classes
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EE_*',
                '',
                array(),
				CoffeeMaker::BREW_SHARED,
				array(),
				array(
					EE_CORE . '*.core.php',
					EE_ADMIN . '*.core.php',
					EE_CPTS . '*.core.php',
					EE_CORE . 'data_migration_scripts' . DS . '*.core.php',
					EE_CORE . 'request_stack' . DS . '*.core.php',
					EE_CORE . 'middleware' . DS . '*.core.php',
				)
			)
		);
		// load admin page parent class
		$this->CoffeeShop->addRecipe(
			new Recipe(
				'EE_Admin_Page*',
                '',
                array(),
				CoffeeMaker::BREW_LOAD_ONLY,
				array(),
				array( EE_ADMIN . '*.core.php' )
			)
		);
		// add a wildcard recipe for loading core classes
		// $this->CoffeeShop->addRecipe(
		// 	new Recipe(
		// 		'*_Admin_Page',
         //        '',
         //        array(),
		// 		CoffeeMaker::BREW_SHARED,
		// 		array(),
		// 		array(
		// 			EE_ADMIN_PAGES . 'transactions' . DS . '*.core.php',
		// 		)
		// 	)
		// );
	}



    public function michaelsTest()
    {
        \EEH_Debug_Tools::printr(__FUNCTION__, __CLASS__, __FILE__, __LINE__, 2);
        echo '<pre style="margin-left:180px;">';
        // HonduranCoffee
        echo '<h4>Add Recipe for using HonduranBean in place of BeanInterface</h4>';
        // have one recipe for HonduranCoffee
        $this->CoffeeShop->addRecipe(
            new Recipe(
                'HonduranBean',
                'EventEspresso\tests\mocks\core\services\container\HonduranBean',
                array(),
                CoffeeMaker::BREW_SHARED,
                array('EventEspresso\tests\mocks\core\services\container\BeanInterface')
            )
        );
        // HonduranCoffee
        echo '<h4>Add Recipe for HonduranCoffee</h4>';
        $this->CoffeeShop->addRecipe(
            new Recipe(
                'HonduranCoffee',
                'EventEspresso\tests\mocks\core\services\container\Coffee'
            )
        );
        /** @var Coffee $HonduranCoffee */
        $HonduranCoffee = $this->CoffeeShop->brew('HonduranCoffee');
        // test it
        echo 'brew HonduranCoffee directly (should be instance of Coffee): ';
        var_dump($HonduranCoffee instanceof Coffee);
        var_dump($HonduranCoffee);
        // test bean type
        echo '<br/>test that bean type is an instance of HonduranBean: ';
        var_dump($HonduranCoffee->getBeans() instanceof HonduranBean);
        var_dump($HonduranCoffee->getBeans());
        // HonduranCoffee
        echo '<br/><h4>Add Recipe for KenyanCoffee</h4>';
        // and another recipe for KenyanCoffee
        $this->CoffeeShop->addRecipe(
            new Recipe(
                'KenyanCoffee',
                'EventEspresso\tests\mocks\core\services\container\Coffee',
                array(), // NO ingredients
                CoffeeMaker::BREW_NEW
            )
        );
        /** @var Coffee $HonduranCoffee */
        $KenyanCoffee = $this->CoffeeShop->brew('KenyanCoffee');
        // test it
        echo 'brew KenyanCoffee directly (should be instance of Coffee): ';
        var_dump($KenyanCoffee instanceof Coffee);
        var_dump($KenyanCoffee);
        // test bean type
        echo '<br/>test that bean type is an instance of HonduranBean: ';
        var_dump($KenyanCoffee->getBeans() instanceof HonduranBean);
        var_dump($KenyanCoffee->getBeans());
        echo '<br/><h4>BUT... It can\'t be KenyanCoffee if it\'s using HonduranBean</h4>';
        echo 'Remove Recipe for KenyanCoffee<br/>';
        $this->CoffeeShop->removeRecipe('KenyanCoffee');
        echo 'and the Closure used for generating instances of KenyanCoffee<br/><br/>';
        $this->CoffeeShop->removeClosure('KenyanCoffee');
        echo '<h4>Now Add NEW Recipe for KenyanCoffee that specifies KenyanBean</h4>';
        // and another recipe for KenyanCoffee
        $this->CoffeeShop->addRecipe(
            new Recipe(
                'KenyanCoffee',
                'EventEspresso\tests\mocks\core\services\container\Coffee',
                array('EventEspresso\tests\mocks\core\services\container\BeanInterface' => 'EventEspresso\tests\mocks\core\services\container\KenyanBean'),
                CoffeeMaker::BREW_NEW
            )
        );
        // test it
        echo 'brew another KenyanCoffee directly: ';
        $KenyanCoffee = $this->CoffeeShop->brew('KenyanCoffee');
        var_dump($KenyanCoffee instanceof Coffee);
        var_dump($KenyanCoffee);
        // test bean type
        echo '<br/>and test that bean type is NOW an instance of KenyanBean: ';
        var_dump($KenyanCoffee->getBeans() instanceof KenyanBean);
        var_dump($KenyanCoffee->getBeans());
        echo '</pre>';
    }


}
// End of file OpenCoffeeShop.php
// Location: /OpenCoffeeShop.php
