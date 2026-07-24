<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\DigitalMenuProductRecommendation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigitalMenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $ingredients = $this->seedIngredients();
            $products = $this->seedProducts($ingredients);
            $this->seedRecommendations($products);
            $this->seedMenus($products);
        });
    }

    /** @return array<string, DigitalMenuIngredient> */
    private function seedIngredients(): array
    {
        $definitions = [
            'coffee' => [['it' => 'Caffè', 'en' => 'Coffee', 'de' => 'Kaffee'], 'kg', 8, 2, 0, 0],
            'milk' => [['it' => 'Latte', 'en' => 'Milk', 'de' => 'Milch'], 'l', 24, 5, .50, 0],
            'orange' => [['it' => 'Arancia', 'en' => 'Orange', 'de' => 'Orange'], 'kg', 18, 4, 0, 0],
            'aperol' => [['it' => 'Aperol', 'en' => 'Aperol', 'de' => 'Aperol'], 'l', 8, 2, 0, 0],
            'prosecco' => [['it' => 'Prosecco', 'en' => 'Prosecco', 'de' => 'Prosecco'], 'l', 15, 3, 0, 0],
            'gin' => [['it' => 'Gin', 'en' => 'Gin', 'de' => 'Gin'], 'l', 7, 2, 1.50, 0],
            'tonic' => [['it' => 'Acqua tonica', 'en' => 'Tonic water', 'de' => 'Tonic Water'], 'pz', 48, 12, 0, 0],
            'tomato' => [['it' => 'Pomodoro', 'en' => 'Tomato', 'de' => 'Tomate'], 'kg', 20, 5, 1, 0],
            'burrata' => [['it' => 'Burrata pugliese', 'en' => 'Apulian burrata', 'de' => 'Burrata aus Apulien'], 'pz', 24, 6, 3, 0],
            'bread' => [['it' => 'Pane e focaccia', 'en' => 'Bread and focaccia', 'de' => 'Brot und Focaccia'], 'kg', 12, 3, 1, 0],
            'olive_oil' => [['it' => 'Olio extravergine di oliva', 'en' => 'Extra virgin olive oil', 'de' => 'Natives Olivenöl extra'], 'l', 18, 4, 0, 0],
            'orecchiette' => [['it' => 'Orecchiette fresche', 'en' => 'Fresh orecchiette', 'de' => 'Frische Orecchiette'], 'kg', 14, 3, 2, 0],
            'turnip_greens' => [['it' => 'Cime di rapa', 'en' => 'Turnip greens', 'de' => 'Stängelkohl'], 'kg', 10, 2, 0, 0],
            'mussels' => [['it' => 'Cozze', 'en' => 'Mussels', 'de' => 'Miesmuscheln'], 'kg', 22, 5, 3, 0],
            'clams' => [['it' => 'Vongole', 'en' => 'Clams', 'de' => 'Venusmuscheln'], 'kg', 12, 3, 4, 0],
            'octopus' => [['it' => 'Polpo', 'en' => 'Octopus', 'de' => 'Oktopus'], 'kg', 15, 4, 5, 0],
            'catch' => [['it' => 'Pescato locale', 'en' => 'Local catch', 'de' => 'Lokaler Fang'], 'kg', 20, 5, 6, 0],
            'squid' => [['it' => 'Calamari', 'en' => 'Squid', 'de' => 'Tintenfisch'], 'kg', 14, 4, 4, 0],
            'shrimp' => [['it' => 'Gamberi', 'en' => 'Prawns', 'de' => 'Garnelen'], 'kg', 12, 3, 5, 0],
            'mozzarella' => [['it' => 'Mozzarella', 'en' => 'Mozzarella', 'de' => 'Mozzarella'], 'kg', 15, 4, 2, 0],
            'pizza_dough' => [['it' => 'Impasto pizza', 'en' => 'Pizza dough', 'de' => 'Pizzateig'], 'pz', 80, 20, 2, 0],
            'capocollo' => [['it' => 'Capocollo di Martina Franca', 'en' => 'Martina Franca capocollo', 'de' => 'Capocollo aus Martina Franca'], 'kg', 8, 2, 3, 0],
            'potato' => [['it' => 'Patate', 'en' => 'Potatoes', 'de' => 'Kartoffeln'], 'kg', 25, 6, 0, 0],
            'mascarpone' => [['it' => 'Mascarpone', 'en' => 'Mascarpone', 'de' => 'Mascarpone'], 'kg', 8, 2, 0, 0],
            'eggs' => [['it' => 'Uova', 'en' => 'Eggs', 'de' => 'Eier'], 'pz', 60, 15, 0, 0],
            'almond' => [['it' => 'Mandorle pugliesi', 'en' => 'Apulian almonds', 'de' => 'Apulische Mandeln'], 'kg', 5, 1, 1, 0],
        ];

        $records = [];
        foreach ($definitions as $key => [$name, $unit, $stock, $minimum, $addPrice, $removePrice]) {
            $ingredient = DigitalMenuIngredient::query()->where('name->it', $name['it'])->first() ?? new DigitalMenuIngredient;
            $ingredient->fill(['name' => $name, 'unit' => $unit, 'stock_quantity' => $stock, 'minimum_quantity' => $minimum, 'add_price' => $addPrice, 'remove_price' => $removePrice, 'tracked' => true, 'is_frozen' => false])->save();
            $records[$key] = $ingredient;
        }

        return $records;
    }

    /** @param array<string, DigitalMenuIngredient> $ingredients
     * @return array<string, DigitalMenuProduct>
     */
    private function seedProducts(array $ingredients): array
    {
        $departments = Department::query()->get()->keyBy(fn (Department $department) => mb_strtolower($department->name));
        $definitions = [
            'espresso' => [['it' => 'Caffè espresso', 'en' => 'Espresso', 'de' => 'Espresso'], ['it' => 'Miscela italiana servita al momento.', 'en' => 'Freshly brewed Italian coffee blend.', 'de' => 'Frisch gebrühte italienische Kaffeemischung.'], 1.50, 'bar', [], ['coffee' => .008]],
            'cappuccino' => [['it' => 'Cappuccino', 'en' => 'Cappuccino', 'de' => 'Cappuccino'], ['it' => 'Espresso con latte fresco montato.', 'en' => 'Espresso with freshly steamed milk.', 'de' => 'Espresso mit frisch aufgeschäumter Milch.'], 2.50, 'bar', ['latte'], ['coffee' => .008, 'milk' => .15]],
            'orange_juice' => [['it' => "Spremuta d'arancia", 'en' => 'Fresh orange juice', 'de' => 'Frisch gepresster Orangensaft'], ['it' => 'Arance fresche spremute al momento.', 'en' => 'Fresh oranges squeezed to order.', 'de' => 'Frisch gepresste Orangen.'], 5, 'bar', [], ['orange' => .4]],
            'spritz' => [['it' => 'Spritz vista mare', 'en' => 'Seaview Spritz', 'de' => 'Spritz mit Meerblick'], ['it' => 'Aperol, prosecco, soda e arancia.', 'en' => 'Aperol, prosecco, soda and orange.', 'de' => 'Aperol, Prosecco, Soda und Orange.'], 8, 'bar', ['solfiti'], ['aperol' => .06, 'prosecco' => .09, 'orange' => .03]],
            'gin_tonic' => [['it' => 'Gin tonic mediterraneo', 'en' => 'Mediterranean gin and tonic', 'de' => 'Mediterraner Gin Tonic'], ['it' => 'Gin, tonica e botaniche mediterranee.', 'en' => 'Gin, tonic and Mediterranean botanicals.', 'de' => 'Gin, Tonic und mediterrane Botanicals.'], 10, 'bar', [], ['gin' => .05, 'tonic' => 1]],
            'beer' => [['it' => 'Birra artigianale pugliese', 'en' => 'Apulian craft beer', 'de' => 'Apulisches Craft-Bier'], ['it' => 'Bionda fresca prodotta in Puglia.', 'en' => 'Fresh lager brewed in Apulia.', 'de' => 'Frisches, in Apulien gebrautes Lager.'], 6, 'bar', ['glutine'], []],
            'verdeca' => [['it' => 'Calice di Verdeca', 'en' => 'Glass of Verdeca', 'de' => 'Glas Verdeca'], ['it' => 'Vino bianco pugliese fresco e minerale.', 'en' => 'Fresh, mineral Apulian white wine.', 'de' => 'Frischer, mineralischer apulischer Weißwein.'], 7, 'bar', ['solfiti'], []],
            'focaccia' => [['it' => 'Focaccia barese', 'en' => 'Bari-style focaccia', 'de' => 'Focaccia nach Bari-Art'], ['it' => 'Pomodorini, olive e olio EVO.', 'en' => 'Cherry tomatoes, olives and extra virgin olive oil.', 'de' => 'Kirschtomaten, Oliven und natives Olivenöl.'], 6, 'pizzeria', ['glutine'], ['bread' => .18, 'tomato' => .08, 'olive_oil' => .015]],
            'frisella' => [['it' => 'Frisella pugliese', 'en' => 'Apulian frisella', 'de' => 'Apulische Frisella'], ['it' => 'Pomodoro, origano, olive e olio EVO.', 'en' => 'Tomato, oregano, olives and extra virgin olive oil.', 'de' => 'Tomate, Oregano, Oliven und natives Olivenöl.'], 9, 'cucina', ['glutine'], ['bread' => .12, 'tomato' => .15, 'olive_oil' => .015]],
            'burrata' => [['it' => 'Burrata e pomodori', 'en' => 'Burrata and tomatoes', 'de' => 'Burrata und Tomaten'], ['it' => 'Burrata di Andria, pomodori e basilico.', 'en' => 'Andria burrata, tomatoes and basil.', 'de' => 'Burrata aus Andria, Tomaten und Basilikum.'], 13, 'cucina', ['latte'], ['burrata' => 1, 'tomato' => .18, 'olive_oil' => .01]],
            'mussels' => [['it' => 'Cozze alla marinara', 'en' => 'Marinara mussels', 'de' => 'Miesmuscheln nach Marinara-Art'], ['it' => 'Cozze, pomodoro, aglio e prezzemolo.', 'en' => 'Mussels, tomato, garlic and parsley.', 'de' => 'Miesmuscheln, Tomate, Knoblauch und Petersilie.'], 14, 'cucina', ['molluschi'], ['mussels' => .5, 'tomato' => .1, 'olive_oil' => .01]],
            'orecchiette' => [['it' => 'Orecchiette alle cime di rapa', 'en' => 'Orecchiette with turnip greens', 'de' => 'Orecchiette mit Stängelkohl'], ['it' => 'Pasta fresca, cime di rapa, acciughe e mollica.', 'en' => 'Fresh pasta, turnip greens, anchovies and breadcrumbs.', 'de' => 'Frische Pasta, Stängelkohl, Sardellen und Brotkrumen.'], 14, 'cucina', ['glutine', 'pesce'], ['orecchiette' => .12, 'turnip_greens' => .15, 'bread' => .02]],
            'seafood_pasta' => [['it' => 'Spaghetti ai frutti di mare', 'en' => 'Seafood spaghetti', 'de' => 'Spaghetti mit Meeresfrüchten'], ['it' => 'Cozze, vongole, gamberi e pomodorini.', 'en' => 'Mussels, clams, prawns and cherry tomatoes.', 'de' => 'Miesmuscheln, Venusmuscheln, Garnelen und Tomaten.'], 19, 'cucina', ['glutine', 'crostacei', 'molluschi'], ['mussels' => .15, 'clams' => .15, 'shrimp' => .1, 'tomato' => .1]],
            'clams_pasta' => [['it' => 'Linguine alle vongole', 'en' => 'Linguine with clams', 'de' => 'Linguine mit Venusmuscheln'], ['it' => 'Vongole dell’Adriatico, aglio e prezzemolo.', 'en' => 'Adriatic clams, garlic and parsley.', 'de' => 'Adriatische Venusmuscheln, Knoblauch und Petersilie.'], 18, 'cucina', ['glutine', 'molluschi'], ['clams' => .3, 'olive_oil' => .015]],
            'octopus' => [['it' => 'Polpo alla brace', 'en' => 'Grilled octopus', 'de' => 'Gegrillter Oktopus'], ['it' => 'Polpo, crema di patate e olio al prezzemolo.', 'en' => 'Octopus, potato cream and parsley oil.', 'de' => 'Oktopus, Kartoffelcreme und Petersilienöl.'], 20, 'cucina', ['molluschi'], ['octopus' => .25, 'potato' => .18, 'olive_oil' => .015]],
            'catch' => [['it' => 'Pescato del giorno alla griglia', 'en' => 'Grilled catch of the day', 'de' => 'Gegrillter Fang des Tages'], ['it' => 'Pesce locale secondo disponibilità, verdure e limone.', 'en' => 'Local fish according to availability, vegetables and lemon.', 'de' => 'Lokaler Fisch nach Verfügbarkeit, Gemüse und Zitrone.'], 24, 'cucina', ['pesce'], ['catch' => .35, 'olive_oil' => .015]],
            'fritto' => [['it' => 'Fritto misto dell’Adriatico', 'en' => 'Adriatic mixed fry', 'de' => 'Gemischter Fischteller aus der Adria'], ['it' => 'Calamari, gamberi e pescato minuto.', 'en' => 'Squid, prawns and small local fish.', 'de' => 'Tintenfisch, Garnelen und kleine lokale Fische.'], 19, 'cucina', ['glutine', 'crostacei', 'molluschi', 'pesce'], ['squid' => .18, 'shrimp' => .12, 'catch' => .1]],
            'margherita' => [['it' => 'Pizza Margherita', 'en' => 'Margherita pizza', 'de' => 'Pizza Margherita'], ['it' => 'Pomodoro, mozzarella, basilico e olio EVO.', 'en' => 'Tomato, mozzarella, basil and extra virgin olive oil.', 'de' => 'Tomate, Mozzarella, Basilikum und Olivenöl.'], 9, 'pizzeria', ['glutine', 'latte'], ['pizza_dough' => 1, 'tomato' => .1, 'mozzarella' => .1]],
            'pugliese_pizza' => [['it' => 'Pizza Pugliese', 'en' => 'Apulian pizza', 'de' => 'Apulische Pizza'], ['it' => 'Pomodoro, mozzarella, burrata e capocollo.', 'en' => 'Tomato, mozzarella, burrata and capocollo.', 'de' => 'Tomate, Mozzarella, Burrata und Capocollo.'], 15, 'pizzeria', ['glutine', 'latte'], ['pizza_dough' => 1, 'tomato' => .1, 'mozzarella' => .08, 'burrata' => 1, 'capocollo' => .05]],
            'salad' => [['it' => 'Insalata mediterranea', 'en' => 'Mediterranean salad', 'de' => 'Mediterraner Salat'], ['it' => 'Verdure fresche, pomodoro, olive e mandorle.', 'en' => 'Fresh vegetables, tomato, olives and almonds.', 'de' => 'Frisches Gemüse, Tomate, Oliven und Mandeln.'], 10, 'cucina', ['frutta a guscio'], ['tomato' => .12, 'almond' => .02, 'olive_oil' => .01]],
            'tiramisu' => [['it' => 'Tiramisù della casa', 'en' => 'House tiramisu', 'de' => 'Hausgemachtes Tiramisu'], ['it' => 'Mascarpone, caffè e cacao.', 'en' => 'Mascarpone, coffee and cocoa.', 'de' => 'Mascarpone, Kaffee und Kakao.'], 7, 'cucina', ['glutine', 'latte', 'uova'], ['mascarpone' => .08, 'coffee' => .01, 'eggs' => 1]],
            'pasticciotto' => [['it' => 'Pasticciotto leccese', 'en' => 'Lecce pasticciotto', 'de' => 'Pasticciotto aus Lecce'], ['it' => 'Frolla fragrante ripiena di crema.', 'en' => 'Fragrant pastry filled with custard.', 'de' => 'Duftendes Gebäck mit Vanillecreme.'], 5, 'bar', ['glutine', 'latte', 'uova'], ['eggs' => 1, 'milk' => .05]],
            'gelato' => [['it' => 'Coppa gelato artigianale', 'en' => 'Artisan gelato cup', 'de' => 'Becher hausgemachtes Eis'], ['it' => 'Tre gusti a scelta.', 'en' => 'Three flavours of your choice.', 'de' => 'Drei Sorten nach Wahl.'], 7, 'bar', ['latte'], ['milk' => .18]],
        ];

        $records = [];
        foreach ($definitions as $key => [$name, $description, $price, $department, $allergens, $recipe]) {
            $product = DigitalMenuProduct::query()->where('name->it', $name['it'])->first() ?? new DigitalMenuProduct;
            $product->fill(['name' => $name, 'description' => $description, 'price' => $price, 'vat' => 10, 'department_id' => $departments->get($department)?->id, 'allergens' => $allergens, 'is_active' => true, 'disallow_takeaway' => false])->save();
            $product->ingredients()->sync(collect($recipe)->mapWithKeys(fn (float|int $quantity, string $ingredientKey) => [$ingredients[$ingredientKey]->id => ['quantity' => $quantity]])->all());
            $records[$key] = $product;
        }

        return $records;
    }

    /** @param array<string, DigitalMenuProduct> $products */
    private function seedRecommendations(array $products): void
    {
        $recommendations = [
            ['espresso', 'pasticciotto', DigitalMenuProductRecommendation::CROSS_SELL, 'checkout'],
            ['spritz', 'focaccia', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['beer', 'focaccia', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['frisella', 'burrata', DigitalMenuProductRecommendation::UPSELL, 'product'],
            ['orecchiette', 'verdeca', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['seafood_pasta', 'verdeca', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['margherita', 'pugliese_pizza', DigitalMenuProductRecommendation::UPSELL, 'product'],
            ['octopus', 'verdeca', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['catch', 'verdeca', DigitalMenuProductRecommendation::CROSS_SELL, 'cart'],
            ['tiramisu', 'espresso', DigitalMenuProductRecommendation::CROSS_SELL, 'checkout'],
        ];

        foreach ($recommendations as $position => [$source, $target, $type, $placement]) {
            DigitalMenuProductRecommendation::updateOrCreate(
                ['product_id' => $products[$source]->id, 'recommended_product_id' => $products[$target]->id, 'type' => $type],
                ['message' => ['it' => $type === 'upsell' ? 'Rendilo ancora più speciale' : 'Completa la tua esperienza', 'en' => $type === 'upsell' ? 'Make it even more special' : 'Complete your experience', 'de' => $type === 'upsell' ? 'Mach es noch besonderer' : 'Vervollständige dein Erlebnis'], 'placement' => $placement, 'position' => $position, 'is_active' => true],
            );
        }
    }

    /** @param array<string, DigitalMenuProduct> $products */
    private function seedMenus(array $products): void
    {
        $menus = [
            ['slug' => 'menu-bar-spiaggia', 'name' => ['it' => 'Menu Bar', 'en' => 'Beach Bar Menu', 'de' => 'Strandbar-Menü'], 'description' => ['it' => 'Caffetteria, aperitivi e sapori pugliesi da gustare in riva al mare.', 'en' => 'Coffee, aperitifs and Apulian flavours to enjoy by the sea.', 'de' => 'Kaffee, Aperitifs und apulische Aromen direkt am Meer.'], 'position' => 1, 'service_charge' => 0, 'theme' => ['primary' => '#0e7490', 'style' => 'coastal'], 'categories' => [
                [['it' => 'Caffetteria', 'en' => 'Coffee', 'de' => 'Kaffee'], ['espresso', 'cappuccino', 'orange_juice']],
                [['it' => 'Aperitivi e cocktail', 'en' => 'Aperitifs and cocktails', 'de' => 'Aperitifs und Cocktails'], ['spritz', 'gin_tonic']],
                [['it' => 'Birre e vini', 'en' => 'Beer and wine', 'de' => 'Bier und Wein'], ['beer', 'verdeca']],
                [['it' => 'Snack e dolci', 'en' => 'Snacks and sweets', 'de' => 'Snacks und Süßes'], ['focaccia', 'frisella', 'pasticciotto', 'gelato']],
            ]],
            ['slug' => 'menu-ristorante-pizzeria', 'name' => ['it' => 'Menu Ristorante Pizzeria', 'en' => 'Restaurant and Pizzeria Menu', 'de' => 'Restaurant- und Pizzeria-Menü'], 'description' => ['it' => 'Cucina mediterranea, pescato locale e pizza con ingredienti di Puglia.', 'en' => 'Mediterranean cuisine, local catch and pizza with Apulian ingredients.', 'de' => 'Mediterrane Küche, lokaler Fang und Pizza mit Zutaten aus Apulien.'], 'position' => 2, 'service_charge' => 2.50, 'theme' => ['primary' => '#0f766e', 'style' => 'editorial'], 'categories' => [
                [['it' => 'Antipasti', 'en' => 'Starters', 'de' => 'Vorspeisen'], ['frisella', 'burrata', 'mussels', 'focaccia']],
                [['it' => 'Primi piatti', 'en' => 'First courses', 'de' => 'Erste Gänge'], ['orecchiette', 'seafood_pasta', 'clams_pasta']],
                [['it' => 'Secondi di mare', 'en' => 'Seafood mains', 'de' => 'Hauptgerichte aus dem Meer'], ['octopus', 'catch', 'fritto']],
                [['it' => 'Pizze', 'en' => 'Pizzas', 'de' => 'Pizzen'], ['margherita', 'pugliese_pizza']],
                [['it' => 'Contorni', 'en' => 'Sides', 'de' => 'Beilagen'], ['salad']],
                [['it' => 'Dolci', 'en' => 'Desserts', 'de' => 'Desserts'], ['tiramisu', 'pasticciotto', 'gelato']],
                [['it' => 'Da bere', 'en' => 'Drinks', 'de' => 'Getränke'], ['verdeca', 'beer', 'spritz']],
            ]],
            ['slug' => 'menu-piatti-del-giorno', 'name' => ['it' => 'Menu Piatti del Giorno', 'en' => 'Daily Specials', 'de' => 'Tagesgerichte'], 'description' => ['it' => 'Le proposte dello chef secondo il pescato e i prodotti freschi del mercato.', 'en' => "The chef's daily selection based on the local catch and fresh market produce.", 'de' => 'Die tägliche Auswahl des Küchenchefs nach Fang und frischen Marktprodukten.'], 'position' => 3, 'service_charge' => 2.50, 'theme' => ['primary' => '#0369a1', 'style' => 'daily'], 'categories' => [
                [['it' => 'Per iniziare', 'en' => 'To start', 'de' => 'Zum Start'], ['burrata', 'mussels']],
                [['it' => 'Primo del giorno', 'en' => 'Daily pasta', 'de' => 'Tagespasta'], ['clams_pasta', 'orecchiette']],
                [['it' => 'Dal mare oggi', 'en' => "Today's catch", 'de' => 'Fang des Tages'], ['catch', 'octopus', 'fritto']],
                [['it' => 'Il dolce di oggi', 'en' => "Today's dessert", 'de' => 'Dessert des Tages'], ['tiramisu', 'pasticciotto']],
            ]],
        ];

        foreach ($menus as $menuData) {
            $categoryDefinitions = $menuData['categories'];
            unset($menuData['categories']);
            $menu = DigitalMenu::updateOrCreate(['slug' => $menuData['slug']], $menuData + ['disclaimer' => ['it' => 'Per allergie o intolleranze rivolgiti al personale.', 'en' => 'Please inform our staff about allergies or intolerances.', 'de' => 'Bitte informieren Sie unser Personal über Allergien oder Unverträglichkeiten.'], 'enabled_languages' => ['it', 'en', 'de'], 'is_published' => true, 'is_visible' => true]);

            foreach ($categoryDefinitions as $position => [$name, $productKeys]) {
                $category = DigitalMenuCategory::updateOrCreate(
                    ['digital_menu_id' => $menu->id, 'position' => $position],
                    ['name' => $name, 'description' => null, 'is_enabled' => true],
                );
                $category->products()->sync(collect($productKeys)->mapWithKeys(fn (string $key, int $productPosition) => [$products[$key]->id => ['menu_price' => null, 'badge' => $productPosition === 0 ? json_encode(['it' => 'Consigliato', 'en' => 'Recommended', 'de' => 'Empfohlen']) : null, 'position' => $productPosition, 'is_visible' => true]])->all());
            }
        }
    }
}
