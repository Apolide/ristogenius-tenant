<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Rooms\RoomCreate;
use App\Livewire\Settings\RoomTables\RoomTableCreate;
use App\Models\Room;
use App\Models\RoomTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoomSettingsLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
    }

    #[DataProvider('locales')]
    public function test_room_and_table_settings_use_the_authenticated_users_language_file(
        string $locale, array $roomList, array $roomForm, array $tableList, array $tableForm, array $plannerTexts,
    ): void {
        $admin = User::factory()->create(['lang'=>$locale, 'enabled'=>true, 'activated_at'=>now()]);
        $admin->assignRole('admin');
        $room = Room::create(['name'=>'Terrazza','active'=>true,'service_charge'=>0,'service_charge_percentage'=>0,'order'=>1,'capacity'=>20,'smoking_allowed'=>false]);
        RoomTable::create(['name'=>'T1','type'=>'quadrato','room_id'=>$room->id,'min_people'=>1,'max_people'=>4]);

        $rooms = $this->actingAs($admin)->get(route('settings.rooms'));
        $rooms->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($roomList as $text) $rooms->assertSee($text);

        $createRoom = Livewire::actingAs($admin)->test(RoomCreate::class)->call('createRoom');
        foreach ($roomForm as $text) $createRoom->assertSee($text);

        $tables = $this->get(route('settings.room-tables'));
        $tables->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($tableList as $text) $tables->assertSee($text);

        $createTable = Livewire::actingAs($admin)->test(RoomTableCreate::class)->call('createRoomTable');
        foreach ($tableForm as $text) $createTable->assertSee($text);

        $planner = $this->get(route('rooms.planner', ['room'=>$room, 'mode'=>'view']));
        $planner->assertOk();
        $this->assertSame($locale, app()->getLocale());
        foreach ($plannerTexts as $text) $planner->assertSee($text);
    }

    public static function locales(): array
    {
        return [
            'Italian room settings translations'=>['it',
                ['Gestione delle sale','ELENCO SALE','Cerca sala','Costo servizio','Capienza','Terrazza'],
                ['NUOVA SALA','Nome sala','La sala è disponibile','Consenti fumatori in questa sala','Salva','Annulla'],
                ['Tavoli','Cerca tavolo o sala','Nome o numero tavolo','Min persone','T1'],
                ['NUOVO TAVOLO','Tipo tavolo','Seleziona sala','Max persone','Salva','Annulla'],
                ['Mappatura sala','Seleziona sala:','Visualizzazione','Modifica','Capacità','Fumatori','Aggiungi tavoli','Aggiungi in griglia']],
            'English room settings translations'=>['en',
                ['Room management','ROOM LIST','Search rooms','Service charge','Capacity','Terrazza'],
                ['NEW ROOM','Room name','The room is available','Allow smoking in this room','Save','Cancel'],
                ['Tables','Search table or room','Table name or number','Min people','T1'],
                ['NEW TABLE','Table type','Select room','Max people','Save','Cancel'],
                ['Room layout','Select room:','View','Edit','Capacity','Smoking','Add tables','Add in grid']],
            'German room settings translations'=>['de',
                ['Raumverwaltung','RAUMLISTE','Räume suchen','Servicegebühr','Kapazität','Terrazza'],
                ['NEUER RAUM','Raumname','Der Raum ist verfügbar','Rauchen in diesem Raum erlauben','Speichern','Abbrechen'],
                ['Tische','Tisch oder Raum suchen','Tischname oder -nummer','Min. Personen','T1'],
                ['NEUER TISCH','Tischtyp','Raum auswählen','Max. Personen','Speichern','Abbrechen'],
                ['Raumplan','Raum auswählen:','Ansicht','Bearbeiten','Kapazität','Raucher','Tische hinzufügen','Im Raster hinzufügen']],
        ];
    }
}
